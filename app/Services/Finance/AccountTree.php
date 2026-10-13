<?php

namespace App\Services\Finance;

use App\Models\Finance\Account\Account;

/**
 * Chart-of-accounts hierarchy for reports (parent / sub-accounts, like Zoho Books): turns the flat list of accounts that have
 * activity into an ordered tree where every parent carries the roll-up of its sub-accounts.
 */
class AccountTree
{
    /**
     * @param  array<int,array>  $rows  trial balance rows: account_code, account_name, account_type, debit, credit
     * @return array<int,array>  depth-first rows: id, parent_id, depth, code, name, type, debit, credit (rolled up),
     *                           own_debit, own_credit, has_children, has_own, ancestors[]
     */
    public static function forTrialBalance(array $rows): array
    {
        $own = [];
        foreach ($rows as $r) {
            $own[$r['account_code']] = [(float) ($r['debit'] ?? 0), (float) ($r['credit'] ?? 0)];
        }

        $accounts = Account::where('is_active', 1)->get(['id', 'code', 'name', 'type', 'parent_id'])->keyBy('id');
        $children = [];
        foreach ($accounts as $a) {
            // a parent that is missing or inactive makes the account a root, so nothing disappears from the report
            $parent = $a->parent_id && $accounts->has($a->parent_id) ? $a->parent_id : 0;
            $children[$parent][] = $a;
        }
        foreach ($children as &$list) {
            usort($list, fn ($x, $y) => strcmp((string) $x->code, (string) $y->code));
        }
        unset($list);

        $out = [];
        $counter = 0;   // pre-order position of each account; rows without activity are dropped afterwards
        $walk = function (int $parentId, int $depth, array $ancestors) use (&$walk, &$children, &$own, &$out, &$counter) {
            $kept = [];
            foreach ($children[$parentId] ?? [] as $a) {
                $slot = $counter++;   // keeps the parent before its sub-accounts; the row is filled once they are rolled up
                [$dr, $cr, $hasKids] = $walk($a->id, $depth + 1, array_merge($ancestors, [$a->id]));
                $o = $own[$a->code] ?? null;
                $dr += $o[0] ?? 0;
                $cr += $o[1] ?? 0;
                if ($o === null && !$hasKids) {
                    continue;   // no activity here or below: not part of the report
                }
                $out[$slot] = [
                    'id' => $a->id, 'parent_id' => $parentId ?: null, 'depth' => $depth, 'code' => $a->code, 'name' => $a->name, 'type' => $a->type,
                    'debit' => $dr, 'credit' => $cr, 'own_debit' => $o[0] ?? 0, 'own_credit' => $o[1] ?? 0,
                    'has_children' => $hasKids, 'has_own' => $o !== null, 'ancestors' => $ancestors,
                ];
                $kept[] = [$dr, $cr];
            }
            // totals of this level and whether anything below it is shown
            return [array_sum(array_column($kept, 0)), array_sum(array_column($kept, 1)), count($kept) > 0];
        };
        $walk(0, 0, []);

        ksort($out);

        return array_values($out);
    }
}
