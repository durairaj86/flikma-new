<?php

namespace App\Livewire\Report\Pack;

use Illuminate\Support\Facades\DB;

class SalesBySalesperson extends PackReport
{
    protected function title(): string { return __('Sales by Salesperson'); }
    protected function subtitle(): string { return __('Jobs and value won by each salesperson'); }
    protected function groups(): array { return ['salesperson' => __('Salesperson'), 'customer' => __('Customer')]; }
    protected function columns(): array { return [__('Jobs'), __('Completed'), __('Job Value')]; }

    protected function rows(): array
    {
        $q = $this->jobs()->leftJoin('users', 'users.id', '=', 'jobs.salesperson_id');
        $g = "COALESCE(users.name, '—')";
        if ($this->groupBy === 'customer') {
            $q->leftJoin('customers', 'customers.id', '=', 'jobs.customer_id');
            $g = "COALESCE(customers.name_en,'—')";
        }

        return $q->selectRaw("$g g, COUNT(*) jobs, SUM(jobs.status = 2) done, SUM(jobs.base_grand_total) val")
            ->groupBy(DB::raw($g))->orderByDesc('val')->get()
            ->map(fn($r) => [$r->g, (int)$r->jobs, (int)$r->done, round($r->val, 2)])->all();
    }
}
