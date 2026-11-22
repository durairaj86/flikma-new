<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModulePermission
{
    /** Context menu items (by id) that act on another module, and the right they need there. */
    private const CROSS_MODULE_ITEMS = [
        'row_job' => ['operations.jobs', 'create'],
        'row_convert_to_job' => ['operations.jobs', 'create'],
        'row_quotation' => ['sales.quotations', 'create'],
        'customer_invoice' => ['finances.customer-invoices', 'create'],
        'supplier_invoice' => ['finances.supplier-invoices', 'create'],
        'proforma_invoice' => ['finances.proforma', 'create'],
        'row_record_payment' => ['transactions.collections', 'create'],
        'row_log_activity' => ['sales.activities', 'create'],
    ];

    /** Context menu items (by id) and the right they need in the row's own module; any other id only reads (view, print, email ...). */
    private const ITEM_ACTIONS = [
        'row_edit' => 'edit', 'row_generate_schedule' => 'edit', 'row_reopen' => 'edit', 'row_close' => 'edit',
        'row_delete' => 'delete',
        'row_duplicate' => 'create', 'row_converted' => 'create', 'row_revise' => 'create',
        'row_approved' => 'approve', 'row_rejected' => 'approve', 'row_disapproved' => 'approve',
        'row_confirmed' => 'confirm',
    ];

    /** Status changes that are not approve / confirm still change the record. */
    private const STATUS_ITEM_PREFIX = 'row_';

    /** Read-only items: never muted. */
    private const READ_ITEMS = [
        'row_view', 'row_print', 'row_print_notice', 'row_print_notification', 'row_email', 'row_download', 'row_search', 'row_statement',
        'row_tracking', 'row_payment_history', 'row_linked', 'row_open', 'row_history',
    ];

    /**
     * Gate every authenticated request against the current user's
     * department rights. The Super User (the person who registered the
     * company) always bypasses this check. Requests to paths that aren't
     * mapped to a module in config/modules.php are left unrestricted.
     * Context menus (".../{id}/actions") come back with the items the user
     * may not use marked disabled, so the menu shows them muted.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || isSuperUser($user)) {
            return $next($request);
        }

        $module = resolveModuleFromPath($request->path());

        if (!$module) {
            return $next($request);
        }

        $action = $this->resolveAction($request, $module);

        if (!userCan($module, $action, $user)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => __('You do not have permission to do this. Contact your administrator.')], 403);
            }
            abort(403, __('You do not have permission to access this section. Contact your administrator.'));
        }

        $response = $next($request);

        if ($response instanceof JsonResponse && str_ends_with(trim($request->path(), '/'), '/actions')) {
            $items = $response->getData(true);
            if (is_array($items) && array_is_list($items)) {
                $response->setData($this->markMenu($items, $module, $user));
            }
        }

        return $response;
    }

    private function resolveAction(Request $request, string $module): string
    {
        return self::actionFor(
            trim($request->path(), '/'),
            $request->method(),
            (string) $request->route()?->getName(),
            count($request->route()?->parameters() ?? []) > 0,
            $module
        );
    }

    /**
     * The right a request needs: view / create / edit / delete / approve / confirm. Public and free of the request so the rights screen
     * can run every route through the same rules (rightActions() in pre-defined-helpers.php) and offer only the actions a section has.
     * $path is the request path, or a route's URI ("masters/bank/{id}/create") when called for a route.
     */
    public static function actionFor(string $path, string $method, string $routeName, bool $hasRouteParam, string $module): string
    {
        $method = strtoupper($method);

        if (str_ends_with($path, '/data') || str_contains($path, '/data/') || str_ends_with($path, '/actions') || str_ends_with($path, '/overview')) {
            return 'view';
        }

        if ($method === 'DELETE' || str_contains($path, '/delete') || str_ends_with($routeName, '.destroy')) {
            return 'delete';
        }

        // a section's own POST actions (period closing: close → approve, reopen → confirm), config/modules.php 'custom_actions'
        $custom = appRights()[$module]['custom_actions'] ?? [];
        if ($method === 'POST' && $custom && isset($custom[$last = basename($path)])) {
            return $custom[$last];
        }

        // undoing or settling an approved document needs the approve right (journal voucher / payment / collection / payroll run disapprove, pay a run)
        if ($method === 'POST' && preg_match('#/(disapprove|approve|pay|pay-installment)$#', $path)) {
            return 'approve';
        }

        if (preg_match('#/status/([^/]+)$#', $path, $m)) {
            return self::statusAction($module, $m[1]);
        }

        // the full-page builder (e.g. invoice/customer/new) and its modal (invoice/customer/gp-form) need the same right as the create modal
        if ($method === 'GET' && (str_ends_with($path, '/new') || str_ends_with($path, '/gp-form'))) {
            return 'create';
        }

        if ($method === 'GET' || $method === 'HEAD') {
            // full-page resource forms (payroll runs, employee loans, employee profiles): create / edit
            if (str_ends_with($routeName, '.create')) return 'create';
            if (str_ends_with($routeName, '.edit')) return 'edit';
        }

        if (str_ends_with($path, '/create') || str_ends_with($path, '/create/quick')) {
            if ($method === 'GET') {
                return 'view';
            }

            return $hasRouteParam ? 'edit' : 'create';
        }

        if ($method === 'GET' || $method === 'HEAD') {
            return 'view';
        }

        // every other write: a resource store / update, or a POST that changes data (import, close, reopen, map, pull ...)
        if (str_ends_with($routeName, '.store')) return 'create';
        if ($method === 'PUT' || $method === 'PATCH' || str_ends_with($routeName, '.update')) return 'edit';
        // POSTs that only read or preview: answer them with the view right
        if (preg_match('#/(preview|check|ask|lookup|search|scan-receipt|scan-bill|suggest-category|template)$#', $path)) return 'view';
        if (preg_match('#/import(/[^/]+)?$#', $path) || str_ends_with($path, '/entries') || str_ends_with($path, '/duplicate') || str_ends_with($path, '/revise')) return 'create';

        return 'edit';
    }

    /** Every right a "/status/{value}" route can need in this section: edit, plus approve / confirm when its status enum has those statuses. */
    public static function statusActions(string $module): array
    {
        $enum = appRights()[$module]['status_enum'] ?? null;
        $actions = ['edit'];
        if ($enum && enum_exists($enum)) {
            foreach ($enum::cases() as $case) {
                $actions[] = self::statusAction($module, (string) $case->value);
            }
        }

        return array_values(array_unique($actions));
    }

    /** "/status/{value}": approving / rejecting a document needs approve, confirming needs confirm, any other status edit. */
    private static function statusAction(string $module, string $value): string
    {
        $enum = appRights()[$module]['status_enum'] ?? null;
        if (!$enum || !ctype_digit($value) || !enum_exists($enum)) {
            return 'edit';
        }

        $case = $enum::tryFrom((int) $value);

        return match ($case?->name) {
            'APPROVED', 'REJECTED', 'DISAPPROVED' => 'approve',
            'CONFIRMED' => 'confirm',
            default => 'edit',
        };
    }

    /** Mark the context menu items this user may not use as disabled (the menu shows them muted and does not run them). */
    private function markMenu(array $items, string $module, $user): array
    {
        foreach ($items as $i => $item) {
            if (!is_array($item)) continue;

            if (($item['type'] ?? '') === 'submenu' && is_array($item['items'] ?? null)) {
                $item['items'] = $this->markMenu($item['items'], $module, $user);
                $usable = array_filter($item['items'], fn ($sub) => is_array($sub) && empty($sub['disabled']));
                if (!$usable) $item['disabled'] = true;
            } elseif (!$this->itemAllowed($item, $module, $user)) {
                $item['disabled'] = true;
            }

            $items[$i] = $item;
        }

        return $items;
    }

    private function itemAllowed(array $item, string $module, $user): bool
    {
        $id = (string) ($item['id'] ?? '');

        if (isset(self::CROSS_MODULE_ITEMS[$id])) {
            [$target, $action] = self::CROSS_MODULE_ITEMS[$id];

            return userCan($target, $action, $user);
        }

        if (in_array($id, self::READ_ITEMS, true) || $id === '') {
            return true;
        }

        // the section's own actions: "row_close" needs what POST ".../close" needs
        $custom = appRights()[$module]['custom_actions'] ?? [];
        if (str_starts_with($id, 'row_') && isset($custom[substr($id, 4)])) {
            return userCan($module, $custom[substr($id, 4)], $user);
        }

        // a status item with the status value it moves to: the same rule as the server's "/status/{value}" (approve / confirm / edit)
        $value = (string) ($item['data-value'] ?? '');
        if (!in_array(self::ITEM_ACTIONS[$id] ?? null, ['edit', 'delete', 'create'], true) && ctype_digit($value) && (appRights()[$module]['status_enum'] ?? null)) {
            return userCan($module, self::statusAction($module, $value), $user);
        }

        $action = self::ITEM_ACTIONS[$id] ?? null;

        // a status item ("Move to" Draft / Sent / Cancelled, Active / In-Active, Block ...) changes the record
        if ($action === null && str_starts_with($id, self::STATUS_ITEM_PREFIX)) {
            $action = 'edit';
        }

        return $action === null || userCan($module, $action, $user);
    }
}
