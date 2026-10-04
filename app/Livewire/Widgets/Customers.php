<?php

namespace App\Livewire\Widgets;

use App\Services\Dashboard\WidgetData;
use Carbon\Carbon;

class Customers extends Widget
{
    protected function module(): string
    {
        return 'customers';
    }

    protected function data(Carbon $start): array
    {
        return WidgetData::customers($start);
    }
}
