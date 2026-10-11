<?php

namespace App\Livewire\Report\Pack;

use Illuminate\Support\Facades\DB;

class ShipmentVolume extends PackReport
{
    protected function title(): string { return __('Shipment Volume'); }
    protected function subtitle(): string { return __('Jobs, containers and TEU by customer, carrier, lane or mode'); }
    protected function groups(): array
    {
        return ['customer' => __('Customer'), 'carrier' => __('Carrier'), 'lane' => __('Lane'), 'mode' => __('Mode')];
    }
    protected function columns(): array { return [__('Jobs'), __('Containers'), __('TEU'), __('Weight (kg)'), __('Volume (CBM)')]; }

    protected function rows(): array
    {
        $g = match ($this->groupBy) {
            'carrier' => "COALESCE(NULLIF(jobs.carrier,''),'—')",
            'lane' => "CONCAT(COALESCE(NULLIF(jobs.origin,''),'?'),' → ',COALESCE(NULLIF(jobs.destination,''),'?'))",
            'mode' => "COALESCE(NULLIF(LOWER(jobs.shipment_mode),''),'—')",
            default => "COALESCE(customers.name_en,'—')",
        };
        $teu = DB::table('job_containers')->selectRaw("job_id, SUM(COALESCE(NULLIF(qty,0),NULLIF(quantity,0),1)) c,
            SUM(COALESCE(NULLIF(qty,0),NULLIF(quantity,0),1) * IF(container_size LIKE '%40%' OR container_size LIKE '%45%',2,1)) teu")
            ->groupBy('job_id');

        return $this->jobs()
            ->leftJoin('customers', 'customers.id', '=', 'jobs.customer_id')
            ->leftJoinSub($teu, 'ct', 'ct.job_id', '=', 'jobs.id')
            ->selectRaw("$g g, COUNT(*) jobs, COALESCE(SUM(ct.c),0) c, COALESCE(SUM(ct.teu),0) teu, SUM(jobs.weight) w, SUM(jobs.volume) v")
            ->groupBy(DB::raw($g))->orderByDesc('jobs')->get()
            ->map(fn($r) => [$r->g, (int)$r->jobs, (int)$r->c, (int)$r->teu, round($r->w, 2), round($r->v, 2)])->all();
    }
}
