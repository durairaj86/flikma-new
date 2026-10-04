<?php

namespace App\Livewire\Widgets;

use App\Services\Dashboard\WidgetData;
use Carbon\Carbon;

class Collection extends Widget
{
    protected function module(): string
    {
        return 'collection';
    }

    protected function data(Carbon $start): array
    {
        return WidgetData::collection($start);
    }
}
