<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CacheController extends Controller
{
    private const COMMANDS = [
        'app' => ['cmd' => 'cache:clear', 'label' => 'Application cache'],
        'config' => ['cmd' => 'config:clear', 'label' => 'Config cache'],
        'route' => ['cmd' => 'route:clear', 'label' => 'Route cache'],
        'view' => ['cmd' => 'view:clear', 'label' => 'View cache'],
        'queue' => ['cmd' => 'queue:restart', 'label' => 'Queue restart'],
    ];

    public function index()
    {
        return view('admin.cache.index', [
            'cacheDriver' => config('cache.default'),
            'opcacheEnabled' => function_exists('opcache_get_status') && @opcache_get_status(false),
            'commands' => self::COMMANDS,
        ]);
    }

    public function clear(Request $request)
    {
        $request->validate(['action' => 'required|in:all,' . implode(',', array_keys(self::COMMANDS))]);
        $todo = $request->action === 'all' ? self::COMMANDS : [self::COMMANDS[$request->action]];

        $results = [];
        foreach ($todo as $def) {
            try {
                Artisan::call($def['cmd']);
                $results[] = ['label' => $def['label'], 'status' => 'success', 'output' => trim(Artisan::output())];
            } catch (\Throwable $e) {
                $results[] = ['label' => $def['label'], 'status' => 'error', 'output' => $e->getMessage()];
            }
        }
        ActivityLog::log('cleared', 'cache', null, $request->action === 'all' ? 'Cleared all caches' : 'Cleared ' . $todo[0]['label']);

        return back()->with('cacheResults', $results);
    }
}
