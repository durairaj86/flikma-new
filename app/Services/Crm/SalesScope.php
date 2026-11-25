<?php

namespace App\Services\Crm;

use App\Models\Master\Salesperson\Salesperson;

/** Which salespeople's work the signed-in user sees on the CRM screens (Pipeline, Tracker). */
class SalesScope
{
    /**
     * A salesman (a user linked to a salesperson, not the Super User or an Admin) only sees his own deals.
     * Null = sees everyone. One login may be linked to several salesperson records.
     */
    public static function ownSalespersonIds(): ?array
    {
        if (isSuperUser() || (int) auth()->user()->role === 3) {
            return null;
        }
        $ids = Salesperson::where('user_id', auth()->id())->pluck('id')->all();

        return $ids ?: null;
    }
}
