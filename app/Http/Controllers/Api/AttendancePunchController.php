<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payroll\AttendanceDevice;
use App\Services\Attendance\PunchNormalizer;
use App\Services\Attendance\PunchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Machine-facing endpoints (no session / CSRF). Two entry points:
 *  - generic HTTP/JSON  : /api/attendance/punch   (Bearer token, any field naming — see PunchNormalizer)
 *  - ZKTeco ADMS "push" : /iclock/cdata           (device identified by its registered serial number)
 */
class AttendancePunchController extends Controller
{
    public function __construct(private PunchService $service, private PunchNormalizer $normalizer)
    {
    }

    public function ping(Request $request): JsonResponse
    {
        $device = $this->deviceFromToken($request);
        if (!$device) {
            return response()->json(['status' => 'error', 'message' => 'Invalid or inactive device token'], 401);
        }
        $this->touch($device, $request);

        return response()->json(['status' => 'ok', 'device' => $device->name, 'server_time' => now($device->timezone)->format('Y-m-d H:i:s')]);
    }

    public function punch(Request $request): JsonResponse
    {
        $device = $this->deviceFromToken($request);
        if (!$device) {
            return response()->json(['status' => 'error', 'message' => 'Invalid or inactive device token'], 401);
        }
        $this->touch($device, $request);

        // JSON body, form fields or query string (some machines can only issue a GET).
        $payload = $request->json()->all() ?: $request->except(['token', 'api_key']);
        if (!$payload && !$request->getContent()) {
            return response()->json(['status' => 'error', 'message' => 'No punch data received'], 422);
        }
        if (!$payload && $request->getContent()) {
            $punches = $this->normalizer->fromZkText($request->getContent(), $device->timezone);   // raw tab-separated lines
        } else {
            $punches = $this->normalizer->fromPayload($payload, $device->timezone, $device->field_map);
        }
        if (!$punches) {
            return response()->json(['status' => 'error', 'message' => 'No valid punches found. Send an employee/user id and a timestamp.'], 422);
        }

        return response()->json(['status' => 'success'] + $this->service->ingest($device, $punches));
    }

    /** Callback / webhook URL unique to a device — the URL itself is the credential (keep it private). */
    public function hook(Request $request, string $publicId): JsonResponse
    {
        $device = AttendanceDevice::withoutGlobalScopes()->where('public_id', $publicId)->where('is_active', true)->first();
        if (!$device) {
            return response()->json(['status' => 'error', 'message' => 'Unknown or inactive callback URL'], 404);
        }
        $this->touch($device, $request);

        $payload = $request->json()->all() ?: $request->all();
        $punches = $payload
            ? $this->normalizer->fromPayload($payload, $device->timezone, $device->field_map)
            : $this->normalizer->fromZkText((string) $request->getContent(), $device->timezone);
        if (!$punches) {
            return response()->json(['status' => 'error', 'message' => 'No valid punches found. Check the field mapping of this device.'], 422);
        }

        return response()->json(['status' => 'success'] + $this->service->ingest($device, $punches));
    }

    /** ZKTeco ADMS: handshake (GET) and attendance upload (POST ?table=ATTLOG). */
    public function zkCdata(Request $request): Response
    {
        $device = $this->deviceFromSerial($request);
        if (!$device) {
            return response('ERROR: unknown device serial number', 403)->header('Content-Type', 'text/plain');
        }
        $this->touch($device, $request);

        if ($request->isMethod('get')) {
            return response(implode("\n", [
                'GET OPTION FROM: ' . $device->serial_number,
                'ErrorDelay=60', 'Delay=30', 'TransTimes=00:00;14:00', 'TransInterval=1',
                'TransFlag=TransData AttLog', 'Realtime=1', 'Encrypt=0',
            ]) . "\n")->header('Content-Type', 'text/plain');
        }

        if (strtoupper((string) $request->query('table')) === 'ATTLOG') {
            $r = $this->service->ingest($device, $this->normalizer->fromZkText($request->getContent(), $device->timezone));
            return response('OK:' . $r['received'])->header('Content-Type', 'text/plain');
        }

        return response('OK')->header('Content-Type', 'text/plain');   // OPERLOG / USERINFO etc. — acknowledged, not used
    }

    public function zkGetRequest(Request $request): Response
    {
        if ($device = $this->deviceFromSerial($request)) {
            $this->touch($device, $request);
        }

        return response('OK')->header('Content-Type', 'text/plain');
    }

    private function deviceFromToken(Request $request): ?AttendanceDevice
    {
        $token = $request->bearerToken() ?: $request->header('X-Device-Token') ?: $request->input('token') ?: $request->input('api_key');

        return AttendanceDevice::findByToken($token);
    }

    private function deviceFromSerial(Request $request): ?AttendanceDevice
    {
        $sn = $request->query('SN') ?: $request->query('sn');

        return $sn ? AttendanceDevice::withoutGlobalScopes()->where('serial_number', $sn)->where('protocol', 'zkteco')->where('is_active', true)->first() : null;
    }

    private function touch(AttendanceDevice $device, Request $request): void
    {
        $device->forceFill(['last_seen_at' => now(), 'last_ip' => $request->ip()])->save();
    }
}
