<?php

namespace App\Console\Commands;

use App\Models\Payroll\AttendanceDevice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/** Sends sample eSSL eBioserver New Web Hook pushes to a device's callback URL (the same requests eBioserver makes). */
class AttendanceDemoWebhook extends Command
{
    protected $signature = 'attendance:demo-webhook {device_id} {--code=S1123 : Employee code} {--password= : Web Hook password, sends the encrypted form} {--base= : Site address, e.g. https://flikma.com (default: APP_URL)}';

    protected $description = 'Send a sample eBioserver Web Hook punch to a device callback URL';

    public function handle(): int
    {
        $device = AttendanceDevice::withoutGlobalScopes()->find($this->argument('device_id'));
        if (!$device) {
            $this->error('Device not found.');

            return self::FAILURE;
        }

        $record = [
            'EmployeeCode' => $this->option('code'),
            'DownloadDate' => now($device->timezone)->format('Y-m-d H:i:s'),
            'LogDate' => now($device->timezone)->format('Y-m-d H:i:s'),
            'DeviceName' => 'SilkBio',
            'SerialNumber' => $device->serial_number ?: 'AEXY182960104',
            'Direction' => 'IN',
            'DeviceDirection' => 'Device',
            'WorkCode' => '0',
            'VerificationType' => 'Finger or Face or Card or Password',
            'GPS' => '0,0',
        ];

        $body = $record;
        if ($password = $this->option('password')) {
            $key = substr(str_pad($password, 32, '1'), 0, 32);
            $body = ['data' => base64_encode(openssl_encrypt(json_encode($record), 'aes-256-cbc', $key, OPENSSL_RAW_DATA, str_repeat("\0", 16)))];
        }

        $url = $this->option('base') ? rtrim($this->option('base'), '/') . '/api/attendance/hook/' . $device->public_id : $device->callbackUrl();
        $res = Http::timeout(20)->post($url, $body);
        $this->line('POST ' . $url);
        $this->line(json_encode($body, JSON_UNESCAPED_SLASHES));
        $this->info('Reply: HTTP ' . $res->status() . ' ' . $res->body());

        return $res->successful() ? self::SUCCESS : self::FAILURE;
    }
}
