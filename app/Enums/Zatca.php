<?php

namespace App\Enums;

enum Zatca: string
{
    const SIMULATION_TEXT = 'simulation';
    const CORE_TEXT = 'core';
    const SIMULATION_MODE = 1;
    const CORE_MODE = 2;
    const INVOICE_TYPE_NAMES = [
        'SIMPLIFIED' => '0200000',
        'TAX' => '0100000',
    ];
    const TEST = true; //only for test (demo,flikma local)
}
