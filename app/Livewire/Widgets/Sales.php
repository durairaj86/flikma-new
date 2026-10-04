<?php

namespace App\Livewire\Widgets;

use App\Services\Dashboard\WidgetData;
use Carbon\Carbon;

class Sales extends Widget
{
    protected function module(): string
    {
        return 'sales';
    }

    protected function data(Carbon $start): array
    {
        return WidgetData::sales($start);
    }
}
