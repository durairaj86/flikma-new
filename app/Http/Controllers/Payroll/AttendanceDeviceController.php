<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\AttendanceDevice;
use App\Models\Payroll\AttendancePunch;
use App\Models\User;
use App\Services\Attendance\DevicePuller;
use App\Services\Attendance\PunchNormalizer;
use App\Services\Attendance\PunchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceDeviceController extends Controller
{
    public function index(): View
    {
        $devices = AttendanceDevice::withCount('punches')->whereNotIn('protocol', ['import', 'manual'])->orderBy('name')->get();
        $timezones = ['Asia/Riyadh', 'Asia/Dubai', 'Asia/Kuwait', 'Asia/Qatar', 'Asia/Bahrain', 'Asia/Muscat', 'Asia/Kolkata', 'Africa/Cairo', 'Europe/London', 'UTC'];
        $unmappedCount = AttendancePunch::where('status', 'unmapped')->distinct('device_user_id')->count('device_user_id');

        return view('modules.payroll.devices.index', compact('devices', 'timezones', 'unmappedCount'));
    }

    public function punchLog(Request $request): View
    {
        $unmapped = AttendancePunch::where('status', 'unmapped')
            ->selectRaw('device_user_id, count(*) as punches, max(punched_at) as last_at')
            ->groupBy('device_user_id')->orderByDesc('last_at')->get();
        $from = formDate($request->query('from'));
        $to = formDate($request->query('to'));
        $recent = AttendancePunch::with(['device:id,name', 'employee:id,name,employee_code'])
            ->when($request->query('status'), fn ($q, $v) => $q->where('status', $v))
            ->when($request->query('employee_id'), fn ($q, $v) => $q->where('employee_id', $v))
            ->when($request->query('device_id'), fn ($q, $v) => $q->where('device_id', $v))
            ->when($request->query('q'), fn ($q, $v) => $q->where('device_user_id', 'like', '%' . trim($v) . '%'))
            ->when($from, fn ($q, $v) => $q->where('punched_at', '>=', $v . ' 00:00:00'))
            ->when($to, fn ($q, $v) => $q->where('punched_at', '<=', $v . ' 23:59:59'))
            ->latest('punched_at')->paginate(50)->withQueryString();
        $devices = AttendanceDevice::orderBy('name')->get(['id', 'name']);
        // Names of the users who added / edited punches by hand (shown in the Remarks column).
        $userIds = $recent->getCollection()->flatMap(fn ($p) => [$p->raw['by'] ?? null, $p->raw['edited_by'] ?? null])->filter()->unique();
        $users = \App\Models\User::withoutGlobalScopes()->whereIn('id', $userIds)->pluck('name', 'id');

        return view('modules.payroll.devices.log', compact('unmapped', 'recent', 'devices', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $device = AttendanceDevice::create($data + ['company_id' => companyId()]);
        $token = $device->protocol === 'api' ? $device->issueToken() : null;

        return redirect()->route('payroll.devices.configure', $device)->with('success', __('Device added. Now complete its connection and field mapping.'))
            ->with('device_token', $token)->with('device_token_name', $device->name);
    }

    /** Per-device setup: connection (our endpoints / their endpoint), field mapping and test. */
    public function configure(AttendanceDevice $device): View
    {
        $employees = User::where('is_employee', true)->whereNull('terminated_at')->orderBy('name')->get(['id', 'name', 'employee_code']);
        $headersText = collect($device->pull_headers ?? [])->map(fn ($v, $k) => "$k: $v")->implode("\n");
        $recent = $device->punches()->with('employee:id,name')->latest('punched_at')->limit(10)->get();

        return view('modules.payroll.devices.configure', compact('device', 'headersText', 'employees', 'recent'));
    }

    public function saveConnection(Request $request, AttendanceDevice $device): RedirectResponse
    {
        $data = $request->validate([
            'mode' => ['required', 'in:push,pull'],
            'pull_url' => ['nullable', 'required_if:mode,pull', 'url', 'max:1000'],
            'pull_method' => ['nullable', 'in:GET,POST'],
            'pull_headers' => ['nullable', 'string', 'max:3000'],
            'pull_params' => ['nullable', 'string', 'max:3000'],
            'pull_interval' => ['nullable', 'integer', 'min:1', 'max:1440'],
        ]);

        $headers = [];
        foreach (preg_split('/\r\n|\r|\n/', (string) ($data['pull_headers'] ?? '')) as $line) {
            if (str_contains($line, ':')) {
                [$k, $v] = array_map('trim', explode(':', $line, 2));
                if ($k !== '') {
                    $headers[$k] = $v;
                }
            }
        }

        $device->update([
            'mode' => $data['mode'],
            'pull_url' => $data['pull_url'] ?? null,
            'pull_method' => $data['pull_method'] ?? 'GET',
            'pull_headers' => $headers ?: null,
            'pull_params' => $data['pull_params'] ?? null,
            'pull_interval' => $data['pull_interval'] ?? 15,
        ]);

        return redirect()->route('payroll.devices.configure', [$device, 'tab' => 'connection'])->with('success', __('Connection settings saved.'));
    }

    public function saveMapping(Request $request, AttendanceDevice $device): RedirectResponse
    {
        $data = $request->validate([
            'records_path' => ['nullable', 'string', 'max:120'],
            'employee_field' => ['nullable', 'string', 'max:120'],
            'employee_match' => ['nullable', 'in:device_user_id,employee_code,employee_id'],
            'time_field' => ['nullable', 'string', 'max:120'],
            'date_field' => ['nullable', 'string', 'max:120'],
            'time_format' => ['nullable', 'string', 'max:60'],
            'direction_field' => ['nullable', 'string', 'max:120'],
            'in_values' => ['nullable', 'string', 'max:200'],
            'out_values' => ['nullable', 'string', 'max:200'],
            'default_direction' => ['nullable', 'in:in,out'],
        ]);
        $device->update(['field_map' => array_filter($data, fn ($v) => $v !== null && $v !== '') ?: null]);

        return redirect()->route('payroll.devices.configure', [$device, 'tab' => 'mapping'])->with('success', __('Field mapping saved.'));
    }

    /** Paste a sample payload from the device → list its fields and show how it would be read (nothing is saved). */
    public function preview(Request $request, AttendanceDevice $device, PunchNormalizer $normalizer)
    {
        $request->validate(['sample' => ['required', 'string', 'max:100000']]);
        $json = json_decode($request->input('sample'), true);
        if (!is_array($json)) {
            return response()->json(['ok' => false, 'message' => __('That is not valid JSON.')], 422);
        }

        // Preview with the mapping currently typed in the form (not yet saved).
        $map = array_filter($request->input('map', []), fn ($v) => $v !== null && $v !== '') ?: $device->field_map;
        $path = $map['records_path'] ?? null;
        $punches = $normalizer->fromPayload($json, $device->timezone, $map);

        return response()->json([
            'ok' => true,
            'fields' => $normalizer->sampleFields($json, $path),
            'punches' => array_map(fn ($p) => [
                'id' => $p['device_user_id'] ?? ('#' . ($p['employee_id'] ?? '?')),
                'time' => $p['punched_at']->format('d-m-Y H:i:s'),
                'direction' => $p['direction'],
            ], array_slice($punches, 0, 10)),
            'count' => count($punches),
        ]);
    }

    public function testPull(AttendanceDevice $device, DevicePuller $puller)
    {
        return response()->json($puller->run($device, false));
    }

    public function pullNow(AttendanceDevice $device, DevicePuller $puller): RedirectResponse
    {
        $r = $puller->run($device, true);

        return redirect()->route('payroll.devices.configure', [$device, 'tab' => 'test'])->with($r['ok'] ? 'success' : 'pull_error', $r['message']);
    }

    public function update(Request $request, AttendanceDevice $device): RedirectResponse
    {
        $device->update($this->validated($request, $device));

        return redirect()->route('payroll.devices.index', ['tab' => 'devices'])->with('success', __('Device updated.'));
    }

    public function regenerate(AttendanceDevice $device): RedirectResponse
    {
        $token = $device->issueToken();

        $fromConfigure = str_contains((string) url()->previous(), '/payroll/devices/' . $device->id);
        $to = $fromConfigure ? redirect()->route('payroll.devices.configure', $device) : redirect()->route('payroll.devices.index', ['tab' => 'devices']);

        return $to->with('success', __('A new API key was generated. The old key stopped working.'))
            ->with('device_token', $token)->with('device_token_name', $device->name);
    }

    public function destroy(AttendanceDevice $device): RedirectResponse
    {
        if ($device->punches()->exists()) {
            return redirect()->route('payroll.devices.index', ['tab' => 'devices'])->withErrors(['device' => __('This device has recorded punches and cannot be deleted. Switch it off instead.')]);
        }
        $device->delete();

        return redirect()->route('payroll.devices.index', ['tab' => 'devices'])->with('success', __('Device deleted.'));
    }

    /** Link a machine PIN to an employee, then re-run every punch that was waiting for it. */
    public function map(Request $request, PunchService $service): RedirectResponse
    {
        $data = $request->validate([
            'device_user_id' => ['required', 'string', 'max:64'],
            'employee_id' => ['required', Rule::exists('users', 'id')->where('company_id', companyId())->where('is_employee', true)],
        ]);

        $clash = User::where('device_user_id', $data['device_user_id'])->where('id', '!=', $data['employee_id'])->first();
        if ($clash) {
            return redirect()->route('payroll.punch-log')->withErrors(['device' => __('Machine ID :id is already assigned to :name.', ['id' => $data['device_user_id'], 'name' => $clash->name])]);
        }

        User::whereKey($data['employee_id'])->update(['device_user_id' => $data['device_user_id']]);
        $n = $service->reprocessUnmapped(companyId());

        return redirect()->route('payroll.punch-log')->with('success', __('Mapped. :n waiting punch(es) were added to attendance.', ['n' => $n]));
    }

    public function reprocess(PunchService $service): RedirectResponse
    {
        $n = $service->reprocessUnmapped(companyId());

        return redirect()->route('payroll.punch-log')->with('success', __(':n punch(es) were matched and added to attendance.', ['n' => $n]));
    }

    private function validated(Request $request, ?AttendanceDevice $device = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'protocol' => ['required', Rule::in(array_keys(AttendanceDevice::PROTOCOLS))],
            'mode' => ['nullable', 'in:push,pull'],
            'serial_number' => ['nullable', 'string', 'max:100', Rule::unique('attendance_devices', 'serial_number')->ignore($device?->id)],
            'timezone' => ['required', 'timezone'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        if ($data['protocol'] === 'zkteco' && empty($data['serial_number'])) {
            throw \Illuminate\Validation\ValidationException::withMessages(['serial_number' => __('ZKTeco devices need their serial number.')]);
        }
        $data['mode'] = $data['protocol'] === 'api' ? ($data['mode'] ?? 'push') : 'push';
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        if ($device === null) {
            $data['is_active'] = true;
        }

        return $data;
    }
}
