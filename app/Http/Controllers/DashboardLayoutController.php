<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Per-user dashboard layout: which widgets are on the dashboard and in what order
 * (drag and drop, add / remove from the widget panel). Stored in user_dashboard_layouts.
 */
class DashboardLayoutController extends Controller
{
    /** Every available widget, in default order. */
    public const WIDGETS = [
        // key => size, module, group (same widget in different sizes), title, icon [, default=false: only from the panel]
        'sales' => ['size' => 'medium', 'module' => 'sales', 'group' => 'sales', 'title' => 'Total Sales', 'icon' => 'bi-currency-dollar'],
        'sales-small' => ['size' => 'small', 'module' => 'sales', 'group' => 'sales', 'title' => 'Total Sales', 'icon' => 'bi-currency-dollar', 'default' => false],
        'quotation' => ['size' => 'small', 'module' => 'sales', 'group' => 'quotation', 'title' => 'Quotation', 'icon' => 'bi-file-earmark-text'],
        'quotation-medium' => ['size' => 'medium', 'module' => 'sales', 'group' => 'quotation', 'title' => 'Quotation', 'icon' => 'bi-file-earmark-text', 'default' => false],
        'enquiry-small' => ['size' => 'small', 'module' => 'sales', 'group' => 'enquiry', 'title' => 'Enquiry', 'icon' => 'bi-envelope-paper', 'default' => false],
        'revenue-summary' => ['size' => 'medium', 'module' => 'sales', 'group' => 'revenue-summary', 'title' => 'Revenue Summary', 'icon' => 'bi-graph-up-arrow'],
        'revenue-expenses' => ['size' => 'medium', 'module' => 'sales', 'group' => 'revenue-expenses', 'title' => 'Revenue vs Expenses', 'icon' => 'bi-bar-chart'],
        'revenue-trend' => ['size' => 'medium', 'module' => 'sales', 'group' => 'revenue-trend', 'title' => 'Revenue Trend', 'icon' => 'bi-activity'],
        'invoices' => ['size' => 'medium', 'module' => 'invoices', 'group' => 'invoices', 'title' => 'Invoices', 'icon' => 'bi-receipt'],
        'invoices-small' => ['size' => 'small', 'module' => 'invoices', 'group' => 'invoices', 'title' => 'Invoices', 'icon' => 'bi-receipt', 'default' => false],
        'awaiting-approval' => ['size' => 'medium', 'module' => 'invoices', 'group' => 'awaiting-approval', 'title' => 'Awaiting Approval', 'icon' => 'bi-clipboard-check'],
        'outstanding' => ['size' => 'medium', 'module' => 'invoices', 'group' => 'outstanding', 'title' => 'Outstanding', 'icon' => 'bi-hourglass-split'],
        'recent-transactions' => ['size' => 'large', 'module' => 'invoices', 'group' => 'recent-transactions', 'title' => 'Recent Transactions', 'icon' => 'bi-clock-history'],
        'customers' => ['size' => 'medium', 'module' => 'customers', 'group' => 'customers', 'title' => 'Customers', 'icon' => 'bi-people'],
        'customers-small' => ['size' => 'small', 'module' => 'customers', 'group' => 'customers', 'title' => 'Customers', 'icon' => 'bi-people', 'default' => false],
        'profit' => ['size' => 'medium', 'module' => 'finance', 'group' => 'profit', 'title' => 'Profit', 'icon' => 'bi-piggy-bank'],
        'profit-small' => ['size' => 'small', 'module' => 'finance', 'group' => 'profit', 'title' => 'Profit', 'icon' => 'bi-piggy-bank', 'default' => false],
        'expenses' => ['size' => 'medium', 'module' => 'finance', 'group' => 'expenses', 'title' => 'Expenses', 'icon' => 'bi-wallet2'],
        'payments' => ['size' => 'small', 'module' => 'finance', 'group' => 'payments', 'title' => 'Payments', 'icon' => 'bi-box-arrow-up-right'],
        'payments-medium' => ['size' => 'medium', 'module' => 'finance', 'group' => 'payments', 'title' => 'Payments', 'icon' => 'bi-box-arrow-up-right', 'default' => false],
        'collection' => ['size' => 'small', 'module' => 'finance', 'group' => 'collection', 'title' => 'Collection', 'icon' => 'bi-box-arrow-in-down-left'],
        'collection-medium' => ['size' => 'medium', 'module' => 'finance', 'group' => 'collection', 'title' => 'Collection', 'icon' => 'bi-box-arrow-in-down-left', 'default' => false],
        'to-collect-pay' => ['size' => 'medium', 'module' => 'finance', 'group' => 'to-collect-pay', 'title' => 'To Collect / To Pay', 'icon' => 'bi-arrow-left-right'],
        'cost-summary' => ['size' => 'small', 'module' => 'finance', 'group' => 'cost-summary', 'title' => 'Cost Summary', 'icon' => 'bi-pie-chart'],
        'job-small' => ['size' => 'small', 'module' => 'operations', 'group' => 'job', 'title' => 'Jobs', 'icon' => 'bi-briefcase', 'default' => false],
        'job-status' => ['size' => 'medium', 'module' => 'operations', 'group' => 'job', 'title' => 'Jobs', 'icon' => 'bi-briefcase'],
        'eta-etd-small' => ['size' => 'small', 'module' => 'operations', 'group' => 'eta-etd', 'title' => 'ETA / ETD', 'icon' => 'bi-ship', 'default' => false],
        'eta-etd' => ['size' => 'large', 'module' => 'operations', 'group' => 'eta-etd', 'title' => 'ETA / ETD', 'icon' => 'bi-ship'],
        'ata-atd-small' => ['size' => 'small', 'module' => 'operations', 'group' => 'ata-atd', 'title' => 'ATA / ATD', 'icon' => 'bi-truck', 'default' => false],
        'ata-atd' => ['size' => 'large', 'module' => 'operations', 'group' => 'ata-atd', 'title' => 'ATA / ATD', 'icon' => 'bi-truck'],
    ];

    public const MODULES = [
        'sales' => ['title' => 'Sales', 'icon' => 'bi-graph-up'],
        'invoices' => ['title' => 'Invoices', 'icon' => 'bi-receipt'],
        'customers' => ['title' => 'Customers', 'icon' => 'bi-people'],
        'finance' => ['title' => 'Finance', 'icon' => 'bi-wallet2'],
        'operations' => ['title' => 'Operations', 'icon' => 'bi-truck'],
    ];

    public static function sizes(): array
    {
        return array_map(fn ($w) => $w['size'], self::WIDGETS);
    }

    /** Catalog for the widget panel: module => [title, icon, groups[group => [title, icon, variants[size => key]]]]. */
    public static function catalog(): array
    {
        $out = [];
        foreach (self::MODULES as $mKey => $module) {
            $out[$mKey] = $module + ['groups' => []];
        }
        foreach (self::WIDGETS as $key => $w) {
            $g = &$out[$w['module']]['groups'][$w['group']];
            $g['title'] = $g['title'] ?? $w['title'];
            $g['icon'] = $g['icon'] ?? $w['icon'];
            $g['variants'][$w['size']] = $key;
            unset($g);
        }
        $order = ['small' => 0, 'medium' => 1, 'large' => 2];
        foreach ($out as &$m) {
            foreach ($m['groups'] as &$g) {
                uksort($g['variants'], fn ($a, $b) => $order[$a] <=> $order[$b]);
            }
        }

        return $out;
    }

    /** Widgets on the user's dashboard, in order. No saved layout yet = every widget. */
    public static function orderFor(?int $userId): array
    {
        $saved = $userId ? DB::table('user_dashboard_layouts')->where('user_id', $userId)->value('layout') : null;
        if ($saved === null) {
            // Alternative sizes are available in the panel but not on the default dashboard.
            return array_keys(array_filter(self::WIDGETS, fn ($w) => ($w['default'] ?? true) !== false));
        }
        $saved = json_decode($saved, true) ?: [];

        return array_values(array_unique(array_filter($saved, fn ($k) => isset(self::WIDGETS[$k]))));
    }

    public function save(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order' => 'present|array|max:100',
            'order.*' => 'string|in:' . implode(',', array_keys(self::WIDGETS)),
        ]);
        $order = array_values(array_unique($data['order']));

        DB::table('user_dashboard_layouts')->updateOrInsert(
            ['user_id' => auth()->id()],
            ['company_id' => companyId(), 'layout' => json_encode($order), 'updated_at' => now(), 'created_at' => now()]
        );

        return response()->json(['status' => 'success']);
    }

    public function reset(): JsonResponse
    {
        DB::table('user_dashboard_layouts')->where('user_id', auth()->id())->delete();

        return response()->json(['status' => 'success']);
    }
}
