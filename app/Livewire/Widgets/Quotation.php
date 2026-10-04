<?php

namespace App\Livewire\Widgets;

use App\Services\Dashboard\WidgetData;
use Carbon\Carbon;

class Quotation extends Widget
{
    protected function module(): string
    {
        return 'quotation';
    }

    protected function data(Carbon $start): array
    {
        return WidgetData::quotation($start);
    }
}
