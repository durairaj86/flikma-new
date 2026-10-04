<?php

namespace App\Livewire\Widgets;

use App\Services\Dashboard\WidgetData;
use Carbon\Carbon;

class Job extends Widget
{
    protected function module(): string
    {
        return 'job';
    }

    protected function data(Carbon $start): array
    {
        return WidgetData::job($start);
    }
}
