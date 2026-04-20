<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Item\Item;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function createItem(Request $request)
    {
        $companyId = companyId();
        $branchId = 1;
        $name = trim($request->input('name'));

        // 1. Check if item already exists
        $existingItem = Item::where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('name_en', $name)
            ->first();

        if ($existingItem) {
            return response()->json([
                'id' => $existingItem->id,
                'name' => $existingItem->name_en
            ]);
        }

        // 2. Create new item
        $lastNumber = Item::where('company_id', $companyId)->max('unique_number');
        $skuCode = ($lastNumber ?? 0) + 1;

        $item = new Item();
        $item->company_id = $companyId;
        $item->branch_id = $branchId;
        $item->unique_number = $skuCode;
        $item->sku_code = 'SKU-' . $companyId . '-' . sprintf('%03d', $skuCode);
        $item->name_en = $name;
        $item->name_ar = $name;
        $item->account_type = 'expense';

        $this->setBaseColumns($item);
        $item->save();

        return response()->json([
            'id' => $item->id,
            'name' => $item->name_en,
            'subtext' => $item->sku_code,
        ]);
    }
}
