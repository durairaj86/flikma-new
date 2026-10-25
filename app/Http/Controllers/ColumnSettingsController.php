<?php

namespace App\Http\Controllers;

use App\Helpers\ModuleDefaultColumns;
use App\Models\ColumnSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ColumnSettingsController extends Controller
{
    public function index(string $page): JsonResponse
    {
        $setting = ColumnSetting::where([
            'page_name'  => $page,
            'user_id'    => Auth::id(),
            'company_id' => companyId(),
        ])->first();

        $defaults = ModuleDefaultColumns::get($page);

        return response()->json([
            'fields'    => $this->translateFields($defaults['fields']),
            'columns'   => $this->translateColumns($setting ? $setting->column_json : $defaults['columns'], $defaults['fields']),
            'is_custom' => (bool) $setting,
        ]);
    }

    public function save(Request $request, string $page): JsonResponse
    {
        $request->validate([
            'columns'            => 'required|array|min:1',
            'columns.*.key'      => 'required|string',
            'columns.*.label'    => 'required|string',
            'columns.*.type'     => 'required|string',
            'columns.*.children' => 'array',
        ]);

        ColumnSetting::updateOrCreate(
            [
                'page_name'  => $page,
                'user_id'    => Auth::id(),
                'company_id' => companyId(),
            ],
            ['column_json' => $request->input('columns')]
        );

        return response()->json(['message' => __('Column settings saved.')]);
    }

    public function reset(string $page): JsonResponse
    {
        ColumnSetting::where([
            'page_name'  => $page,
            'user_id'    => Auth::id(),
            'company_id' => companyId(),
        ])->delete();

        $defaults = ModuleDefaultColumns::get($page);

        return response()->json([
            'message' => __('Reset to defaults.'),
            'columns' => $this->translateColumns($defaults['columns'], $defaults['fields']),
        ]);
    }

    /** labels are stored in English; show them in the user's language */
    private function translateFields(array $fields): array
    {
        return array_map(fn ($f) => array_merge($f, [
            'label' => __($f['label'] ?? ''),
            'category' => isset($f['category']) ? __($f['category']) : null,
        ]), $fields);
    }

    /** a known field takes its label from the defaults by key (a layout saved in another language still reads right) */
    private function translateColumns(array $columns, array $fields): array
    {
        $labels = collect($fields)->pluck('label', 'key');
        return array_map(function ($c) use ($labels) {
            if (isset($c['key'])) $c['label'] = __($labels[$c['key']] ?? ($c['label'] ?? ''));
            if (!empty($c['children']) && is_array($c['children'])) $c['children'] = $this->translateColumns($c['children'], $labels->map(fn ($l, $k) => ['key' => $k, 'label' => $l])->values()->all());
            return $c;
        }, $columns);
    }
}
