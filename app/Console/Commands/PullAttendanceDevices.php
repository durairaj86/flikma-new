<?php

namespace App\Console\Commands;

use App\Models\Payroll\AttendanceDevice;
use App\Services\Attendance\DevicePuller;
use Illuminate\Console\Command;

class PullAttendanceDevices extends Command
{
    protected $signature = 'attendance:pull';
    protected $description = 'Poll punch devices / vendor APIs configured in "pull" mode and import their punches';

    public function handle(DevicePuller $puller): int
    {
        AttendanceDevice::withoutGlobalScopes()->where('is_active', true)->where('protocol', 'api')->where('mode', 'pull')->get()
            ->filter->isDue()->each(function (AttendanceDevice $d) use ($puller) {
                $r = $puller->run($d, true);
                $this->line("[{$d->company_id}] {$d->name}: " . $r['message']);
            });

        return self::SUCCESS;
    }
}
