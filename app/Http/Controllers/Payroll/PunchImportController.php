<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Services\Attendance\PunchImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PunchImportController extends Controller
{
    private const TIMEZONES = ['Asia/Riyadh', 'Asia/Dubai', 'Asia/Kuwait', 'Asia/Qatar', 'Asia/Bahrain', 'Asia/Muscat', 'Asia/Kolkata', 'Africa/Cairo', 'Europe/London', 'UTC'];

    public function __construct(private PunchImportService $importer)
    {
    }

    public function form(): View
    {
        return view('modules.payroll.devices.import');
    }

    public function template()
    {
        return response()->streamDownload(function () {
            $f = fopen('php://output', 'w');
            fputcsv($f, ['Employee ID', 'Date Time', 'Direction']);
            fputcsv($f, ['17', '2026-10-11 08:02:10', 'IN']);
            fputcsv($f, ['17', '2026-10-11 17:31:45', 'OUT']);
            fputcsv($f, ['21', '2026-10-11 08:10:03', '']);
            fclose($f);
        }, 'punch_import_template.csv', ['Content-Type' => 'text/csv']);
    }

    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120'],
        ], [
            'file.required' => __('Choose a file to upload.'),
            'file.mimes' => __('Only Excel (.xlsx, .xls) or CSV files are accepted.'),
            'file.max' => __('The file is larger than 5 MB.'),
        ]);

        try {
            $data = $this->importer->read($request->file('file'));
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => __('The file could not be read. Make sure it is a valid Excel / CSV file.')]);
        }

        $token = Str::random(40);
        Storage::disk('local')->put("punch-imports/$token.json", json_encode([
            'company_id' => companyId(), 'user_id' => auth()->id(), 'name' => $request->file('file')->getClientOriginalName(),
            'header' => $data['header'], 'rows' => $data['rows'], 'map' => $this->importer->guessMapping($data['header']),
            'timezone' => 'Asia/Riyadh', 'created' => now()->timestamp,
        ], JSON_INVALID_UTF8_SUBSTITUTE));

        return redirect()->route('payroll.punch-import.preview', $token);
    }

    public function preview(Request $request, string $token): View|RedirectResponse
    {
        $meta = $this->load($token);
        if (!$meta) {
            return redirect()->route('payroll.punch-import')->withErrors(['file' => __('This import has expired. Upload the file again.')]);
        }
        [$map, $tz] = $this->settings($request, $meta);
        $analysis = $this->importer->analyse($meta['rows'], $map, $tz, companyId());

        return view('modules.payroll.devices.import-preview', [
            'token' => $token, 'meta' => $meta, 'map' => $map, 'tz' => $tz, 'timezones' => self::TIMEZONES,
            'analysis' => $analysis, 'sample' => array_slice($meta['rows'], 0, 3),
        ]);
    }

    public function confirm(Request $request, string $token): RedirectResponse
    {
        $meta = $this->load($token);
        if (!$meta) {
            return redirect()->route('payroll.punch-import')->withErrors(['file' => __('This import has expired. Upload the file again.')]);
        }
        [$map, $tz] = $this->settings($request, $meta);

        $analysis = $this->importer->analyse($meta['rows'], $map, $tz, companyId());
        if ($analysis['fatal']) {
            return back()->withErrors(['import' => $analysis['fatal']]);
        }
        if ($analysis['counts']['error'] > 0 && !$request->boolean('skip_errors')) {
            return back()->withInput()->withErrors(['import' => __(':n row(s) have errors. Tick "Skip the rows with errors" to import the rest, or fix the file and upload again.', ['n' => $analysis['counts']['error']])]);
        }
        if ($analysis['counts']['ok'] + $analysis['counts']['unmapped'] + $analysis['counts']['locked'] === 0) {
            return back()->withErrors(['import' => __('There is nothing to import: no valid rows.')]);
        }

        $r = $this->importer->import($meta['rows'], $map, $tz, companyId());
        Storage::disk('local')->delete("punch-imports/$token.json");

        $res = $r['result'];
        return redirect()->route('payroll.punch-log')->with('success', __(':p punch(es) added to attendance, :u saved without an employee match, :l skipped (payroll generated), :d duplicate(s) ignored, :e row(s) with errors skipped.', [
            'p' => $res['processed'], 'u' => $res['unmapped'], 'l' => $res['locked'], 'd' => $analysis['counts']['duplicate'], 'e' => $analysis['counts']['error'],
        ]));
    }

    /** Mapping comes from the form (query/post) when present, otherwise from the auto-detected defaults. */
    private function settings(Request $request, array $meta): array
    {
        $map = $meta['map'];
        if ($request->has('map')) {
            $in = (array) $request->input('map');
            foreach (['id', 'datetime', 'date', 'time', 'direction'] as $k) {
                $v = $in[$k] ?? null;
                $map[$k] = ($v === null || $v === '' || !ctype_digit((string) $v) || (int) $v >= count($meta['header'])) ? null : (int) $v;
            }
            $map['match'] = in_array($in['match'] ?? '', ['device_user_id', 'employee_code', 'employee_id'], true) ? $in['match'] : 'device_user_id';
            $map['time_format'] = substr(trim((string) ($in['time_format'] ?? 'auto')) ?: 'auto', 0, 40);
        }
        $tz = in_array($request->input('timezone'), self::TIMEZONES, true) ? $request->input('timezone') : $meta['timezone'];

        return [$map, $tz];
    }

    private function load(string $token): ?array
    {
        if (!preg_match('/^[A-Za-z0-9]{40}$/', $token) || !Storage::disk('local')->exists("punch-imports/$token.json")) {
            return null;
        }
        $meta = json_decode(Storage::disk('local')->get("punch-imports/$token.json"), true);

        return $meta && ($meta['company_id'] ?? null) === companyId() ? $meta : null;
    }
}
