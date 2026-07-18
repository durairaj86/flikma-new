<?php

namespace App\Http\Controllers\Job;

use App\Enums\JobEnum;
use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Job\Job;
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
            'invoicedJobs' => $this->getInvoicedJobsCount($startDate, $endDate),
            'cancelledJobs' => $this->getCancelledJobs($startDate, $endDate),
            'customersCount' => $this->getCustomersCount($startDate, $endDate),
            'fromQuotations' => $this->getJobsFromQuotationsCount($startDate, $endDate),
            'repeatRatio' => $this->getRepeatCustomerRatio($startDate, $endDate),
            'avgContainersPerJob' => $this->getAvgContainersPerJob($startDate, $endDate),
            'jobsTrend' => $this->getJobsTrend($startDate, $endDate, $range),
            'jobSource' => $this->getJobSourceBreakdown($startDate, $endDate),
            'completionRateTrend' => $this->getCompletionRateTrend($startDate, $endDate, $range),
            'topCarriers' => $this->getTopCarriers($startDate, $endDate),
            'invoicingCoverage' => $this->getInvoicingCoverage($startDate, $endDate),
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

    /**
     * Jobs that have at least one customer invoice raised against them —
     * links Job to the Customer Invoice module.
     */
    private function getInvoicedJobsCount($startDate, $endDate): int
    {
        $jobIds = Job::whereBetween('created_at', [$startDate, $endDate])->pluck('id');

        return (int) CustomerInvoice::whereIn('job_id', $jobIds)
            ->distinct('job_id')
            ->count('job_id');
    }

    /**
     * Jobs created directly from an accepted Quotation — links Job to the
     * Sales (Enquiry/Quotation) pipeline.
     */
    private function getJobsFromQuotationsCount($startDate, $endDate): int
    {
        return (int) Job::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('quotation_id')
            ->count();
    }

    private function getJobSourceBreakdown($startDate, $endDate): array
    {
        $total = Job::whereBetween('created_at', [$startDate, $endDate])->count();
        $fromQuotation = $this->getJobsFromQuotationsCount($startDate, $endDate);
        $direct = max(0, $total - $fromQuotation);

        return [
            ['label' => 'From Quotation', 'value' => $fromQuotation],
            ['label' => 'Direct', 'value' => $direct],
        ];
    }

    private function getInvoicingCoverage($startDate, $endDate): array
    {
        $total = Job::whereBetween('created_at', [$startDate, $endDate])->count();
        $invoiced = $this->getInvoicedJobsCount($startDate, $endDate);
        $notInvoiced = max(0, $total - $invoiced);

        return [
            ['label' => 'Invoiced', 'value' => $invoiced],
            ['label' => 'Not Yet Invoiced', 'value' => $notInvoiced],
        ];
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

    /**
     * % of jobs completed per period — a quality/throughput trend distinct
     * from the raw volume shown in the Jobs Trend chart.
     */
    private function getCompletionRateTrend($startDate, $endDate, $range): array
    {
        $trend = [];

        if ($range === 'this_year') {
            for ($i = 1; $i <= 12; $i++) {
                $monthStart = Carbon::create(Carbon::now()->year, $i, 1)->startOfMonth();
                $monthEnd = Carbon::create(Carbon::now()->year, $i, 1)->endOfMonth();

                $total = Job::whereBetween('created_at', [$monthStart, $monthEnd])->count();
                $completed = Job::whereBetween('created_at', [$monthStart, $monthEnd])
                    ->where('status', JobEnum::COMPLETED->value)->count();

                $trend[] = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

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

                $total = Job::whereBetween('created_at', [$weekStart, $weekEnd])->count();
                $completed = Job::whereBetween('created_at', [$weekStart, $weekEnd])
                    ->where('status', JobEnum::COMPLETED->value)->count();

                $trend[] = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

                $currentDate->addDays(7);
                $weekNumber++;

                if ($weekNumber > 5) break;
            }
        }

        return $trend;
    }

    private function getTopCarriers($startDate, $endDate): array
    {
        return Job::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('carrier')
            ->where('carrier', '!=', '')
            ->select('carrier as label', DB::raw('COUNT(*) as value'))
            ->groupBy('carrier')
            ->orderBy('value', 'desc')
            ->take(6)
            ->get()
            ->map(fn($r) => ['label' => $r->label, 'value' => (int) $r->value])
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
