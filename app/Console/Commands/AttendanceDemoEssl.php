<?php

namespace App\Console\Commands;

use App\Models\Payroll\AttendanceDevice;
use App\Models\User;
use App\Services\Attendance\PunchService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/** Creates a demo "eSSL eBioserver" terminal and a few days of in/out punches for existing employees. */
class AttendanceDemoEssl extends Command
{
    protected $signature = 'attendance:demo-essl {company_id : Company to seed} {--days=5}';

    protected $description = 'Add a demo eSSL eBioserver device with sample punches';

    public function handle(PunchService $service): int
    {
        $companyId = (int) $this->argument('company_id');
        $employees = User::withoutGlobalScopes()->where('company_id', $companyId)->where('is_employee', true)
            ->whereNull('terminated_at')->orderBy('id')->limit(5)->get();
        if ($employees->isEmpty()) {
            $this->error('No employees found for that company.');

            return self::FAILURE;
        }

        $device = AttendanceDevice::withoutGlobalScopes()->firstOrCreate(
            ['serial_number' => 'ESSL-DEMO-' . $companyId],
            ['company_id' => $companyId, 'name' => 'eBioserver demo terminal', 'protocol' => 'essl', 'mode' => 'push', 'timezone' => 'Asia/Riyadh', 'is_active' => true]
        );
        $device->forceFill(['last_seen_at' => now(), 'last_ip' => '192.168.1.50'])->save();

        $punches = [];
        foreach ($employees as $i => $employee) {
            if (!$employee->device_user_id) {
                $employee->forceFill(['device_user_id' => (string) (100 + $i)])->save();
            }
            $pin = (string) $employee->device_user_id;
            for ($d = (int) $this->option('days'); $d >= 1; $d--) {
                $day = Carbon::now($device->timezone)->subDays($d);
                if ($day->isFriday()) {
                    continue;
                }
                $punches[] = ['device_user_id' => $pin, 'punched_at' => $day->copy()->setTime(8, 0 + $i * 3, 0), 'direction' => 'in', 'raw' => ['demo' => true]];
                $punches[] = ['device_user_id' => $pin, 'punched_at' => $day->copy()->setTime(17, 0 + $i * 2, 0), 'direction' => 'out', 'raw' => ['demo' => true]];
            }
        }

        $r = $service->ingest($device, $punches);
        $this->info("Device #{$device->id} ready. " . json_encode($r));

        return self::SUCCESS;
    }
}
