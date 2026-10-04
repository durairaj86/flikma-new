<?php

namespace App\Livewire\Widgets;

use App\Services\Dashboard\WidgetData;
use Carbon\Carbon;

class Profit extends Widget
{
    protected function module(): string
    {
        return 'profit';
    }

    protected function data(Carbon $start): array
    {
        return WidgetData::profit($start);
    }
}
