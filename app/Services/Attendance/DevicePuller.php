<?php

namespace App\Services\Attendance;

use App\Models\Payroll\AttendanceDevice;
use Illuminate\Support\Facades\Http;

/**
 * "Pull" mode: for machines / vendor clouds that expose their own API, we call it on a schedule,
 * run the answer through the device's column mapping and feed the punches into attendance.
 *
 * URL, headers and parameters may use these tokens:
 *   {from} / {to}               Y-m-d H:i:s in the device's time zone
 *   {from_date} / {to_date}     Y-m-d
 *   {from_epoch} / {to_epoch}   unix seconds
 */
class DevicePuller
{
    public function __construct(private PunchNormalizer $normalizer, private PunchService $service)
    {
    }

    /**
     * @return array{ok:bool, message:string, http_status?:int, body?:string, fields?:array, punches?:array, result?:array}
     */
    public function run(AttendanceDevice $device, bool $save, ?array $range = null): array
    {
        if (!$device->pull_url) {
            return ['ok' => false, 'message' => __('Enter the API endpoint first.')];
        }
        if ($device->isSoap()) {
            return $this->runSoap($device, $save, $range);
        }
        if ($range) {
            return ['ok' => false, 'message' => __('Importing a date range is available for the eBioserver web service only.')];
        }

        $tz = $device->timezone ?: 'Asia/Riyadh';
        $to = now($tz);
        $from = $device->last_pulled_at ? $device->last_pulled_at->copy()->setTimezone($tz)->subMinutes(5) : $to->copy()->subDay();
        $t = [
            '{from}' => $from->format('Y-m-d H:i:s'), '{to}' => $to->format('Y-m-d H:i:s'),
            '{from_date}' => $from->format('Y-m-d'), '{to_date}' => $to->format('Y-m-d'),
            '{from_epoch}' => (string) $from->timestamp, '{to_epoch}' => (string) $to->timestamp,
            '{from_iso}' => $from->format('Y-m-d\TH:i:s'), '{to_iso}' => $to->format('Y-m-d\TH:i:s'),
        ];
        $sub = fn (?string $v) => strtr((string) $v, $t);

        $url = $sub($device->pull_url);
        if ($err = $this->unsafeUrl($url)) {
            return $this->finish($device, $save, ['ok' => false, 'message' => $err]);
        }

        $headers = [];
        foreach (($device->pull_headers ?? []) as $k => $v) {
            if ($k === 'WebhookPassword') {
                continue;   // our own setting, never sent to the vendor
            }
            $headers[$k] = $sub((string) $v);
        }
        $params = trim($sub($device->pull_params));

        $send = function (string $params) use ($device, $headers, $url) {
            $req = Http::timeout(25)->withHeaders($headers)->acceptJson();
            if (strtoupper($device->pull_method ?: 'GET') === 'POST') {
                $isJson = str_starts_with($params, '{') || str_starts_with($params, '[');
                $req = $isJson ? $req->withBody($params, 'application/json') : $req->asForm();

                return $isJson ? $req->post($url) : $req->post($url, $this->parseQuery($params));
            }

            return $req->get($url . ($params !== '' ? (str_contains($url, '?') ? '&' : '?') . $params : ''));
        };

        try {
            $res = $send($params);
        } catch (\Throwable $e) {
            return $this->finish($device, $save, ['ok' => false, 'message' => __('Could not reach the endpoint: :m', ['m' => $e->getMessage()])]);
        }

        $body = (string) $res->body();
        if (!$res->successful()) {
            return $this->finish($device, $save, ['ok' => false, 'http_status' => $res->status(), 'body' => mb_substr($body, 0, 4000),
                'message' => __('The endpoint answered HTTP :s.', ['s' => $res->status()])]);
        }

        $json = json_decode($body, true);
        if (is_array($json)) {
            $punches = $this->normalizer->fromPayload($json, $tz, $device->field_map);
            $fields = $this->normalizer->sampleFields($json, $device->field_map['records_path'] ?? null);
        } else {
            $punches = $this->normalizer->fromZkText($body, $tz);   // plain text / tab separated lines
            $fields = [];
        }

        // APIs paged with a "CurrentIndex" body field (eBioServer REST): keep asking for the next page until it is empty or repeats.
        $paged = strtoupper($device->pull_method ?: 'GET') === 'POST' ? json_decode($params, true) : null;
        if ($punches && is_array($paged) && array_key_exists('CurrentIndex', $paged)) {
            $seen = md5($body);
            for ($page = (int) $paged['CurrentIndex'] + 1, $n = 0; $n < 19; $page++, $n++) {
                try {
                    $next = $send(json_encode(array_merge($paged, ['CurrentIndex' => $page])));
                } catch (\Throwable) {
                    break;
                }
                $nextBody = (string) $next->body();
                $nextJson = json_decode($nextBody, true);
                if (!$next->successful() || !is_array($nextJson) || md5($nextBody) === $seen) {
                    break;
                }
                $more = $this->normalizer->fromPayload($nextJson, $tz, $device->field_map);
                if (!$more) {
                    break;
                }
                $punches = array_merge($punches, $more);
                $seen = md5($nextBody);
            }
        }

        $out = ['ok' => true, 'http_status' => $res->status(), 'body' => mb_substr($body, 0, 4000), 'fields' => $fields,
            'punches' => array_map(fn ($p) => [
                'id' => $p['device_user_id'] ?? ('#' . ($p['employee_id'] ?? '?')),
                'time' => $p['punched_at']->format('d-m-Y H:i:s'),
                'direction' => $p['direction'],
            ], array_slice($punches, 0, 10))];

        if (!$punches) {
            $out['message'] = __('Connected, but no punches could be read. Check the field mapping.');
            return $this->finish($device, $save, $out + ['ok' => $save ? false : true]);
        }

        if ($save) {
            $out['result'] = $this->service->ingest($device, $punches);
            $out['message'] = __(':n punch(es) received, :p added to attendance.', ['n' => $out['result']['received'], 'p' => $out['result']['processed']]);
        } else {
            $out['message'] = __('Connected. :n punch(es) found — nothing saved (test only).', ['n' => count($punches)]);
        }

        return $this->finish($device, $save, $out);
    }

    /** eBioserver New web service: SOAP GetTransactionsLog(FromDateTime, ToDateTime, SerialNumber, UserName, UserPassword) → strDataList. */
    private function runSoap(AttendanceDevice $device, bool $save, ?array $range = null): array
    {
        $tz = $device->timezone ?: 'Asia/Riyadh';
        $to = now($tz);
        $from = $device->last_pulled_at ? $device->last_pulled_at->copy()->setTimezone($tz)->subMinutes(10) : $to->copy()->subDay();
        if ($range) {
            [$from, $to] = [$range[0]->copy()->startOfDay(), $range[1]->copy()->endOfDay()];
        }
        if ($err = $this->unsafeUrl($device->pull_url)) {
            return $this->finish($device, $save, ['ok' => false, 'message' => $err]);
        }
        $cred = $device->pull_headers ?? [];
        if (empty($cred['UserName']) || empty($cred['UserPassword'])) {
            return $this->finish($device, $save, ['ok' => false, 'message' => __('Enter the web service user name and password first.')]);
        }

        $x = fn (?string $v) => htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $ebio = $this->isEbioWebService($device->pull_url);
        $envelope = fn (string $op, string $inner) => '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body>'
            . '<' . $op . ' xmlns="http://tempuri.org/">' . $inner . '</' . $op . '></soap:Body></soap:Envelope>';

        try {
            if ($ebio) {
                $res = null;
                // eBioserver New (webservice.asmx). Records: "LogId,DateTime,EmployeeCode,Device,Location,Direction;".
                // The real import reads new logs by log number (kept in field_map.last_log_id); repeated punches are ignored by ingest().
                $call = function (string $op, string $extra) use ($device, $cred, $x, $envelope) {
                    $inner = '<UserName>' . $x($cred['UserName']) . '</UserName><Password>' . $x($cred['UserPassword']) . '</Password>' . $extra;
                    $r = Http::timeout(30)->withHeaders(['SOAPAction' => '"http://tempuri.org/' . $op . '"'])
                        ->withBody($envelope($op, $inner), 'text/xml; charset=utf-8')->post($device->pull_url);
                    $b = (string) $r->body();

                    return $r->successful() && preg_match('~<' . $op . 'Result[^>]*>(.*?)</' . $op . 'Result>~s', $b, $m)
                        ? html_entity_decode($m[1]) : '(HTTP ' . $r->status() . ')';
                };
                $loc = '<Location>' . $x($device->pull_params) . '</Location>';
                $text = '';
                $status = '';
                $report = null;
                $newLastId = null;

                if ($range) {
                    // One-time import of a chosen period: every day of it, whatever was fetched before.
                    for ($day = $from->copy()->startOfDay(); $day->lte($to) && $day->diffInDays($from) < 31; $day->addDay()) {
                        $chunk = $call('GetDeviceLogs', $loc . '<LogDate>' . $day->format('Y-m-d') . '</LogDate>');
                        if (str_starts_with($chunk, '(HTTP')) {
                            $status = $chunk;
                            break;
                        }
                        $text .= "\n" . $chunk;
                    }
                } elseif ($save) {
                    $last = (int) ($device->field_map['last_log_id'] ?? 0);
                    for ($i = 0; $i < 10; $i++) {
                        $chunk = $call('GetDeviceLogsByLogId', $loc . '<LogId>' . $last . '</LogId><LogCount>500</LogCount>');
                        $rows = $this->parseSoapLog($chunk, $tz);
                        if (!$rows) {
                            $status = trim(strip_tags($chunk));
                            break;
                        }
                        $text .= "\n" . $chunk;
                        $ids = array_filter(array_map(fn ($r) => $r['raw']['log_id'] ?? null, $rows));
                        $max = $ids ? max($ids) : $last;
                        if ($max <= $last) {
                            break;
                        }
                        $last = $newLastId = $max;
                        if (count($rows) < 500) {
                            break;
                        }
                    }
                } else {
                    $days = $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay());
                    for ($i = 0; $i <= min($days, 7); $i++) {
                        $chunk = $call('GetDeviceLogs', $loc . '<LogDate>' . $from->copy()->addDays($i)->format('Y-m-d') . '</LogDate>');
                        $text .= "\n" . $chunk;
                    }
                    $byId = $call('GetDeviceLogsByLogId', $loc . '<LogId>0</LogId><LogCount>20</LogCount>');
                    $list = $call('GetDeviceList', $loc);
                    $report = "GetDeviceLogs (days):\n" . (trim(strip_tags($text)) ?: '(empty)')
                        . "\n\nGetDeviceLogsByLogId:\n" . ($byId ?: '(empty)') . "\n\nGetDeviceList:\n" . ($list ?: '(empty)');
                    if (trim(strip_tags($text)) === '' && $this->parseSoapLog($byId, $tz)) {
                        $text = $byId;
                    }
                }
            } else {
                $xml = $envelope('GetTransactionsLog',
                    '<FromDateTime>' . $from->format('Y-m-d H:i') . '</FromDateTime><ToDateTime>' . $to->format('Y-m-d H:i') . '</ToDateTime>'
                    . '<SerialNumber>' . $x($device->serial_number) . '</SerialNumber>'
                    . '<UserName>' . $x($cred['UserName']) . '</UserName><UserPassword>' . $x($cred['UserPassword']) . '</UserPassword><strDataList></strDataList>');
                $res = Http::timeout(30)->withHeaders(['SOAPAction' => '"http://tempuri.org/GetTransactionsLog"'])
                    ->withBody($xml, 'text/xml; charset=utf-8')->post($device->pull_url);
                $body = (string) $res->body();
            }
        } catch (\Throwable $e) {
            return $this->finish($device, $save, ['ok' => false, 'message' => __('Could not reach the endpoint: :m', ['m' => $e->getMessage()])]);
        }

        if (!$ebio && !$res->successful()) {
            return $this->finish($device, $save, ['ok' => false, 'http_status' => $res->status(), 'body' => mb_substr($body, 0, 4000),
                'message' => __('The endpoint answered HTTP :s.', ['s' => $res->status()])]);
        }

        if (!$ebio) {
            $status = '';
            $text = '';
            if (preg_match('~<GetTransactionsLogResult[^>]*>(.*?)</GetTransactionsLogResult>~s', $body, $m)) {
                $status = trim(html_entity_decode($m[1]));
            }
            if (preg_match('~<strDataList[^>]*>(.*?)</strDataList>~s', $body, $m)) {
                $text = html_entity_decode($m[1]);
            }
        }
        if ($ebio) {
            $body = $report ?? $text;
            if (str_starts_with(ltrim($status), '(HTTP')) {
                return $this->finish($device, $save, ['ok' => false, 'body' => $status, 'message' => __('The endpoint answered HTTP :s.', ['s' => trim($status, '() HTTP')])]);
            }
        }
        $punches = $this->parseSoapLog($text, $tz);
        if ($ebio && $save && !$range) {
            // A first sync of a long-running server should not drag in years of history.
            $punches = array_values(array_filter($punches, fn ($p) => $p['punched_at']->gte(now()->subDays(35))));
            if ($newLastId !== null) {
                $device->forceFill(['field_map' => array_merge($device->field_map ?? [], ['last_log_id' => $newLastId])])->save();
            }
        }

        $out = ['ok' => true, 'http_status' => $res?->status() ?? 200, 'body' => mb_substr($body, 0, 4000), 'fields' => [],
            'punches' => array_map(fn ($p) => [
                'id' => $p['device_user_id'],
                'time' => $p['punched_at']->format('d-m-Y H:i:s'),
                'direction' => $p['direction'],
            ], array_slice($punches, 0, 10))];

        if (!$punches) {
            $out['message'] = __('Connected, but no punches could be read.') . ($status !== '' ? ' ' . __('Service said: :s', ['s' => mb_substr($status, 0, 200)]) : '');
            return $this->finish($device, $save, $out + ['ok' => !$save]);
        }
        if ($save) {
            $out['result'] = $this->service->ingest($device, $punches);
            $out['message'] = __(':n punch(es) received, :p added to attendance.', ['n' => $out['result']['received'], 'p' => $out['result']['processed']]);
        } else {
            $out['message'] = __('Connected. :n punch(es) found — nothing saved (test only).', ['n' => count($punches)]);
        }

        return $this->finish($device, $save, $out);
    }

    /**
     * The service returns the log as text; the exact layout depends on the eBioserver version, so each record is read loosely:
     * the first token that is not a date/time is the employee code, the date/time token the time, and in / out words the direction.
     */
    private function isEbioWebService(string $url): bool
    {
        return stripos($url, 'webservice.asmx') !== false && stripos($url, 'webapiservice.asmx') === false;
    }

    /** XML result (DataSet-like rows) → punches through the generic field detection; otherwise one record per text line. */
    private function parseSoapLog(string $text, string $tz): array
    {
        if (str_contains($text, '<') && ($xml = @simplexml_load_string('<r>' . $text . '</r>'))) {
            $rows = [];
            $walk = function (\SimpleXMLElement $n) use (&$rows, &$walk) {
                $kids = $n->children();
                if (count($kids) === 0) {
                    return;
                }
                $leaf = true;
                foreach ($kids as $k) {
                    $leaf = $leaf && count($k->children()) === 0;
                }
                if ($leaf && count($kids) >= 2) {
                    $rows[] = array_map('strval', (array) $n);
                    return;
                }
                foreach ($kids as $k) {
                    $walk($k);
                }
            };
            $walk($xml);
            if ($rows) {
                return $this->normalizer->fromPayload(['records' => $rows], $tz, null);
            }
        }
        $out = [];
        foreach (preg_split('/\r\n|\r|\n|<br\s*\/?>|;(?=\s*\S)/i', $text) as $line) {
            $line = trim($line, " \t\r\n;");
            if (preg_match('~^(\d+),(\d{4}-\d{2}-\d{2} \d{2}:\d{2}(?::\d{2})?),([^,;]+)(?:,([^,;]*))?(?:,([^,;]*))?(?:,([^,;]*))?;?$~', $line, $r)) {
                try {
                    $at = \Illuminate\Support\Carbon::parse($r[2], $tz);
                } catch (\Throwable) {
                    continue;
                }
                $dir = match (strtoupper(trim($r[6] ?? ''))) {
                    'IN', 'CHECKIN', 'I' => 'in',
                    'OUT', 'CHECKOUT', 'O' => 'out',
                    default => null,   // "INOUT" = the device does not say; the day's first / last punch decides
                };
                $out[] = ['device_user_id' => trim($r[3]), 'punched_at' => $at, 'direction' => $dir,
                    'raw' => ['log_id' => (int) $r[1], 'device' => trim($r[4] ?? ''), 'location' => trim($r[5] ?? ''), 'line' => $line]];
                continue;
            }
            if ($line === '' || !preg_match('~(\d{4}-\d{2}-\d{2}|\d{2}[-/]\d{2}[-/]\d{4})[ T]+(\d{1,2}:\d{2}(?::\d{2})?)~', $line, $dt)) {
                continue;
            }
            $rest = trim(str_replace($dt[0], ' ', $line));
            $id = null;
            $dir = null;
            foreach (preg_split('/[\s,|\t]+/', $rest) as $tok) {
                if ($tok === '') {
                    continue;
                }
                if ($dir === null && preg_match('/^(check-?in|in|i)$/i', $tok)) {
                    $dir = 'in';
                } elseif ($dir === null && preg_match('/^(check-?out|out|o)$/i', $tok)) {
                    $dir = 'out';
                } elseif ($id === null) {
                    $id = $tok;
                }
            }
            if ($id === null) {
                continue;
            }
            try {
                $date = preg_match('~^\d{4}~', $dt[1]) ? $dt[1] : str_replace('/', '-', $dt[1]);
                $at = \Illuminate\Support\Carbon::parse($date . ' ' . $dt[2], $tz);
            } catch (\Throwable) {
                continue;
            }
            $out[] = ['device_user_id' => $id, 'punched_at' => $at, 'direction' => $dir, 'raw' => ['line' => $line]];
        }

        return $out;
    }

    private function finish(AttendanceDevice $device, bool $save, array $out): array
    {
        if ($save) {
            $device->forceFill([
                'last_pulled_at' => now(), 'last_seen_at' => $out['ok'] ? now() : $device->last_seen_at,
                'last_pull_status' => $out['ok'] ? 'ok' : 'error', 'last_pull_message' => mb_substr($out['message'], 0, 500),
            ])->save();
        }

        return $out;
    }

    private function parseQuery(string $q): array
    {
        parse_str($q, $arr);

        return $arr;
    }

    /** Server-side requests to user-supplied URLs: refuse non-http(s) and internal network addresses. */
    private function unsafeUrl(string $url): ?string
    {
        $p = parse_url($url);
        if (!$p || !in_array($p['scheme'] ?? '', ['http', 'https'], true) || empty($p['host'])) {
            return __('The endpoint must be a full http:// or https:// address.');
        }
        if (app()->isLocal()) {
            return null;
        }
        $ips = filter_var($p['host'], FILTER_VALIDATE_IP) ? [$p['host']] : (gethostbynamel($p['host']) ?: []);
        if (!$ips) {
            return __('The endpoint host could not be resolved.');
        }
        foreach ($ips as $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return __('Internal / private network addresses are not allowed.');
            }
        }

        return null;
    }
}
