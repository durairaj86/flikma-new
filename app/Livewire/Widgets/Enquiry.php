<?php

namespace App\Livewire\Widgets;

use App\Services\Dashboard\WidgetData;
use Carbon\Carbon;

class Enquiry extends Widget
{
    protected function module(): string
    {
        return 'enquiry';
    }

    protected function data(Carbon $start): array
    {
        return WidgetData::enquiry($start);
    }
}
