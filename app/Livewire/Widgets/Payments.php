<?php

namespace App\Livewire\Widgets;

use App\Services\Dashboard\WidgetData;
use Carbon\Carbon;

class Payments extends Widget
{
    protected function module(): string
    {
        return 'payments';
    }

    protected function data(Carbon $start): array
    {
        return WidgetData::payments($start);
    }
}
