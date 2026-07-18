<?php

namespace App\Http\Controllers\Job;

use App\Enums\JobEnum;
use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Job\Job;
use App\Models\Master\LogisticActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobOverviewController extends Controller
{
    private function getRange($request): array
    {
        $range = $request->input('range', 'this_month');

        return match ($range) {
            'last_month' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth(), $range],
            'this_year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear(), $range],
            default => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth(), $range],
        };
    }

    public function index(Request $request)
    {
        [$startDate, $endDate, $range] = $this->getRange($request);

        $data = [
            'totalJobs' => $this->getTotalJobs($startDate, $endDate),
            'completedJobs' => $this->getCompletedJobs($startDate, $endDate),
            'pendingJobs' => $this->getPendingJobs(),
            'avgContainersPerJob' => $this->getAvgContainersPerJob($startDate, $endDate),
            'cancelledJobs' => $this->getCancelledJobs($startDate, $endDate),
            'customersCount' => $this->getCustomersCount($startDate, $endDate),
            'repeatRatio' => $this->getRepeatCustomerRatio($startDate, $endDate),
            'jobsTrend' => $this->getJobsTrend($startDate, $endDate, $range),
            'serviceTypes' => $this->getJobsByServiceType($startDate, $endDate),
            'shipmentModes' => $this->getJobsByShipmentMode($startDate, $endDate),
            'handledBy' => $this->getHandledByPerformance($startDate, $endDate),
            'customers' => $this->getTopCustomers($startDate, $endDate),
            'routes' => $this->getTopRoutes($startDate, $endDate),
            'jobStatuses' => $this->getJobStatusBreakdown($startDate, $endDate),
            'monthlyComparison' => $this->getMonthlyComparison(),
        ];

        return view('modules.job.overview', compact('data', 'range'));
    }

    private function getTotalJobs($startDate, $endDate): int
    {
        return (int) Job::whereBetween('created_at', [$startDate, $endDate])->count();
    }

    private function getCompletedJobs($startDate, $endDate): int
    {
        return (int) Job::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', JobEnum::COMPLETED->value)
            ->count();
    }

    private function getPendingJobs(): int
    {
        return (int) Job::where('status', JobEnum::PENDING->value)->count();
    }

    private function getCancelledJobs($startDate, $endDate): int
    {
        return (int) Job::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', JobEnum::CANCELLED->value)
            ->count();
    }

    private function getAvgContainersPerJob($startDate, $endDate): float
    {
        $jobs = Job::whereBetween('created_at', [$startDate, $endDate])
            ->withCount('containers')
            ->get(['id']);

        $count = $jobs->count();
        if ($count === 0) {
            return 0;
        }

        return $jobs->sum('containers_count') / $count;
    }

    private function getCustomersCount($startDate, $endDate): int
    {
        return (int) Job::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('customer_id')
            ->distinct('customer_id')
            ->count('customer_id');
    }

    private function getRepeatCustomerRatio($startDate, $endDate): float
    {
        $totalCustomers = (int) Job::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('customer_id')
            ->distinct('customer_id')
            ->count('customer_id');

        $repeat = (int) Job::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('customer_id')
            ->select('customer_id', DB::raw('COUNT(*) as job_count'))
            ->groupBy('customer_id')
            ->having('job_count', '>', 1)
            ->count();

        return $totalCustomers > 0 ? $repeat / $totalCustomers : 0;
    }

    private function getJobsTrend($startDate, $endDate, $range): array
    {
        $trend = [];

        if ($range === 'this_year') {
            for ($i = 1; $i <= 12; $i++) {
                $monthStart = Carbon::create(Carbon::now()->year, $i, 1)->startOfMonth();
                $monthEnd = Carbon::create(Carbon::now()->year, $i, 1)->endOfMonth();

                $trend[] = Job::whereBetween('created_at', [$monthStart, $monthEnd])->count();

                if ($monthStart->month === Carbon::now()->month) {
                    break;
                }
            }
        } else {
            $currentDate = clone $startDate;
            $weekNumber = 1;

            while ($currentDate->lte($endDate)) {
                $weekStart = clone $currentDate;
                $weekEnd = (clone $currentDate)->addDays(6)->min($endDate);

                $trend[] = Job::whereBetween('created_at', [$weekStart, $weekEnd])->count();

                $currentDate->addDays(7);
                $weekNumber++;

                if ($weekNumber > 5) break;
            }
        }

        return $trend;
    }

    private function getJobsByServiceType($startDate, $endDate): array
    {
        $activities = LogisticActivity::activities()->keyBy('id');

        return Job::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('activity_id')
            ->select('activity_id', DB::raw('COUNT(*) as value'))
            ->groupBy('activity_id')
            ->orderBy('value', 'desc')
            ->take(6)
            ->get()
            ->map(fn($r) => [
                'label' => $activities[$r->activity_id]->name ?? "Activity #{$r->activity_id}",
                'value' => (int) $r->value,
            ])
            ->toArray();
    }

    private function getJobsByShipmentMode($startDate, $endDate): array
    {
        return Job::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('shipment_mode')
            ->where('shipment_mode', '!=', '')
            ->select('shipment_mode as label', DB::raw('COUNT(*) as value'))
            ->groupBy('shipment_mode')
            ->orderBy('value', 'desc')
            ->take(6)
            ->get()
            ->map(fn($r) => ['label' => ucfirst($r->label), 'value' => (int) $r->value])
            ->toArray();
    }

    private function getHandledByPerformance($startDate, $endDate): array
    {
        return Job::whereBetween('jobs.created_at', [$startDate, $endDate])
            ->whereNotNull('jobs.created_by')
            ->join('users', 'jobs.created_by', '=', 'users.id')
            ->select('users.name', DB::raw('COUNT(*) as value'))
            ->groupBy('users.id', 'users.name')
            ->orderBy('value', 'desc')
            ->take(5)
            ->get()
            ->map(fn($r) => ['name' => $r->name, 'value' => (int) $r->value])
            ->toArray();
    }

    private function getTopCustomers($startDate, $endDate): array
    {
        $jobs = Job::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('customer_id')
            ->withCount(['containers', 'packages'])
            ->get(['id', 'customer_id']);

        $jobsByCustomer = $jobs->groupBy('customer_id');
        $customers = Customer::whereIn('id', $jobsByCustomer->keys())->get()->keyBy('id');

        return $jobsByCustomer->map(fn($customerJobs, $customerId) => [
            'name' => $customers[$customerId]->name ?? "Customer #{$customerId}",
            'jobs' => $customerJobs->count(),
            'containers' => (int) $customerJobs->sum('containers_count'),
            'packages' => (int) $customerJobs->sum('packages_count'),
        ])
        ->sortByDesc('jobs')
        ->take(10)
        ->values()
        ->toArray();
    }

    private function getTopRoutes($startDate, $endDate): array
    {
        return Job::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('pol')
            ->whereNotNull('pod')
            ->where('pol', '!=', '')
            ->where('pod', '!=', '')
            ->select('pol', 'pod', DB::raw('COUNT(*) as job_count'))
            ->groupBy('pol', 'pod')
            ->orderBy('job_count', 'desc')
            ->take(10)
            ->get()
            ->map(fn($r) => [
                'route' => $r->pol . ' → ' . $r->pod,
                'jobs' => (int) $r->job_count,
            ])
            ->toArray();
    }

    private function getJobStatusBreakdown($startDate, $endDate): array
    {
        return Job::whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function ($r) {
                $enum = JobEnum::tryFrom($r->status);
                return [
                    'status' => $enum ? strtolower($enum->name) : (string) $r->status,
                    'label' => $enum?->label() ?? (string) $r->status,
                    'count' => (int) $r->count,
                ];
            })
            ->toArray();
    }

    private function getMonthlyComparison(): array
    {
        $currentMonth = [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
        $lastMonth = [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()];

        $currentCount = (int) Job::whereBetween('created_at', $currentMonth)->count();
        $lastCount = (int) Job::whereBetween('created_at', $lastMonth)->count();

        $currentCompleted = (int) Job::whereBetween('created_at', $currentMonth)
            ->where('status', JobEnum::COMPLETED->value)->count();
        $lastCompleted = (int) Job::whereBetween('created_at', $lastMonth)
            ->where('status', JobEnum::COMPLETED->value)->count();

        return [
            'current' => ['jobs' => $currentCount, 'completed' => $currentCompleted],
            'previous' => ['jobs' => $lastCount, 'completed' => $lastCompleted],
        ];
    }
}
