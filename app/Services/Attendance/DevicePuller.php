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
    public function run(AttendanceDevice $device, bool $save): array
    {
        if (!$device->pull_url) {
            return ['ok' => false, 'message' => __('Enter the API endpoint first.')];
        }
        if ($device->isSoap()) {
            return $this->runSoap($device, $save);
        }

        $tz = $device->timezone ?: 'Asia/Riyadh';
        $to = now($tz);
        $from = $device->last_pulled_at ? $device->last_pulled_at->copy()->setTimezone($tz)->subMinutes(5) : $to->copy()->subDay();
        $t = [
            '{from}' => $from->format('Y-m-d H:i:s'), '{to}' => $to->format('Y-m-d H:i:s'),
            '{from_date}' => $from->format('Y-m-d'), '{to_date}' => $to->format('Y-m-d'),
            '{from_epoch}' => (string) $from->timestamp, '{to_epoch}' => (string) $to->timestamp,
        ];
        $sub = fn (?string $v) => strtr((string) $v, $t);

        $url = $sub($device->pull_url);
        if ($err = $this->unsafeUrl($url)) {
            return $this->finish($device, $save, ['ok' => false, 'message' => $err]);
        }

        $headers = [];
        foreach (($device->pull_headers ?? []) as $k => $v) {
            $headers[$k] = $sub((string) $v);
        }
        $params = trim($sub($device->pull_params));

        try {
            $req = Http::timeout(25)->withHeaders($headers)->acceptJson();
            if (strtoupper($device->pull_method ?: 'GET') === 'POST') {
                $req = str_starts_with($params, '{') || str_starts_with($params, '[')
                    ? $req->withBody($params, 'application/json')
                    : $req->asForm();
                $res = str_starts_with($params, '{') || str_starts_with($params, '[') ? $req->post($url) : $req->post($url, $this->parseQuery($params));
            } else {
                $res = $req->get($url . ($params !== '' ? (str_contains($url, '?') ? '&' : '?') . $params : ''));
            }
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
    private function runSoap(AttendanceDevice $device, bool $save): array
    {
        $tz = $device->timezone ?: 'Asia/Riyadh';
        $to = now($tz);
        $from = $device->last_pulled_at ? $device->last_pulled_at->copy()->setTimezone($tz)->subMinutes(10) : $to->copy()->subDay();
        if ($err = $this->unsafeUrl($device->pull_url)) {
            return $this->finish($device, $save, ['ok' => false, 'message' => $err]);
        }
        $cred = $device->pull_headers ?? [];
        if (empty($cred['UserName']) || empty($cred['UserPassword'])) {
            return $this->finish($device, $save, ['ok' => false, 'message' => __('Enter the web service user name and password first.')]);
        }

        $x = fn (?string $v) => htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $xml = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body>'
            . '<GetTransactionsLog xmlns="http://tempuri.org/">'
            . '<FromDateTime>' . $from->format('Y-m-d H:i') . '</FromDateTime><ToDateTime>' . $to->format('Y-m-d H:i') . '</ToDateTime>'
            . '<SerialNumber>' . $x($device->serial_number) . '</SerialNumber>'
            . '<UserName>' . $x($cred['UserName']) . '</UserName><UserPassword>' . $x($cred['UserPassword']) . '</UserPassword>'
            . '<strDataList></strDataList></GetTransactionsLog></soap:Body></soap:Envelope>';

        try {
            $res = Http::timeout(30)->withHeaders(['SOAPAction' => '"http://tempuri.org/GetTransactionsLog"'])
                ->withBody($xml, 'text/xml; charset=utf-8')->post($device->pull_url);
        } catch (\Throwable $e) {
            return $this->finish($device, $save, ['ok' => false, 'message' => __('Could not reach the endpoint: :m', ['m' => $e->getMessage()])]);
        }

        $body = (string) $res->body();
        if (!$res->successful()) {
            return $this->finish($device, $save, ['ok' => false, 'http_status' => $res->status(), 'body' => mb_substr($body, 0, 4000),
                'message' => __('The endpoint answered HTTP :s.', ['s' => $res->status()])]);
        }

        $status = '';
        $text = '';
        if (preg_match('~<GetTransactionsLogResult[^>]*>(.*?)</GetTransactionsLogResult>~s', $body, $m)) {
            $status = trim(html_entity_decode($m[1]));
        }
        if (preg_match('~<strDataList[^>]*>(.*?)</strDataList>~s', $body, $m)) {
            $text = html_entity_decode($m[1]);
        }
        $punches = $this->parseSoapLog($text, $tz);

        $out = ['ok' => true, 'http_status' => $res->status(), 'body' => mb_substr($body, 0, 4000), 'fields' => [],
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
    private function parseSoapLog(string $text, string $tz): array
    {
        $out = [];
        foreach (preg_split('/\r\n|\r|\n|<br\s*\/?>|;(?=\s*\S)/i', $text) as $line) {
            $line = trim($line);
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
