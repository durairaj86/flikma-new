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
            'fields'    => $defaults['fields'],
            'columns'   => $setting ? $setting->column_json : $defaults['columns'],
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

        return response()->json(['message' => 'Column settings saved.']);
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
            'message' => 'Reset to defaults.',
            'columns' => $defaults['columns'],
        ]);
    }
}
