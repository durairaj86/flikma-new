<?php

namespace App\Livewire\Widgets;

use App\Services\Dashboard\WidgetData;
use Carbon\Carbon;

class Invoices extends Widget
{
    protected function module(): string
    {
        return 'invoices';
    }

    protected function data(Carbon $start): array
    {
        return WidgetData::invoices($start);
    }
}
