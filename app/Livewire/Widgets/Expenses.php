<?php

namespace App\Livewire\Widgets;

use App\Services\Dashboard\WidgetData;
use Carbon\Carbon;

class Expenses extends Widget
{
    protected function module(): string
    {
        return 'expense';
    }

    protected function data(Carbon $start): array
    {
        return WidgetData::expense($start);
    }
}
