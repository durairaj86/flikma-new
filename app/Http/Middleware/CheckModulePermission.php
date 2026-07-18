<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModulePermission
{
    /**
     * Gate every authenticated request against the current user's
     * department rights. The Super User (the person who registered the
     * company) always bypasses this check. Requests to paths that aren't
     * mapped to a module in config/modules.php are left unrestricted.
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

        $action = $this->resolveAction($request);

        if (!userCan($module, $action, $user)) {
            abort(403, 'You do not have permission to access this section. Contact your administrator.');
        }

        return $next($request);
    }

    private function resolveAction(Request $request): string
    {
        $path = trim($request->path(), '/');
        $method = $request->method();
        $hasRouteParam = count($request->route()?->parameters() ?? []) > 0;

        if (str_ends_with($path, '/data') || str_ends_with($path, '/actions') || str_ends_with($path, '/overview')) {
            return 'view';
        }

        if ($method === 'DELETE' || str_contains($path, '/delete')) {
            return 'delete';
        }

        if (str_contains($path, '/status/')) {
            return 'edit';
        }

        if (str_ends_with($path, '/create')) {
            if ($method === 'GET') {
                return 'view';
            }

            return $hasRouteParam ? 'edit' : 'create';
        }

        return 'view';
    }
}
