<?php

namespace App\Import;

use App\Enums\CustomerStatusEnum;
use App\Models\Customer\Customer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class CustomerExcelImport implements ToModel, WithStartRow
{
    protected $mapping;
    protected $errors = [];
    protected $imported = 0;
    protected $nextRowNo;

    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
        // Fetch max once to avoid duplicate key issues during the transaction
        $this->nextRowNo = (Customer::max('unique_row_no') ?? 0);
    }

    public function model(array $row)
    {
        try {
            $data = [];

            // Map the Excel row to our field keys
            foreach ($this->mapping as $field => $columnIndex) {
                if ($columnIndex !== null && $columnIndex !== '') {
                    $data[$field] = $row[$columnIndex] ?? null;
                }
            }

            // Clean data and skip if all mapped fields are empty
            $filteredData = array_filter($data, fn($value) => !is_null($value) && $value !== '');
            if (empty($filteredData)) {
                return null;
            }

            // Increment row number in memory
            $this->nextRowNo++;

            // Set default values
            $data['unique_row_no'] = $this->nextRowNo;
            $data['row_no'] = 'CS' . sprintf("%03d", $this->nextRowNo);
            $data['company_id'] = companyId();
            $data['user_id'] = auth()->id();
            $data['currency'] = 'SAR';
            $data['status'] = CustomerStatusEnum::PENDING->value;
            $data['business_type'] = 'unregistered';

            if (!empty($data['vat_number'])) {
                $data['business_type'] = 'registered';
            }

            // Validation logic
            $rules = [];
            if (isset($data['name_en'])) {
                $rules['name_en'] = [
                    'required',
                    Rule::unique('customers', 'name_en')->where('company_id', $data['company_id'])
                ];
            }

            if (!empty($data['vat_number'])) {
                $rules['vat_number'] = [
                    'required',
                    Rule::unique('customers', 'vat_number')->where('company_id', $data['company_id'])
                ];
            }

            if (!empty($rules)) {
                $validator = Validator::make($data, $rules);
                if ($validator->fails()) {
                    // Log the specific row error for the user
                    $this->errors[] = "Row error: " . implode(', ', $validator->errors()->all());
                    return null;
                }
            }

            $this->imported++;

            // IMPORTANT: Ensure these fields are in your Customer model's $fillable array
            return new Customer($data);

        } catch (\Exception $e) {
            $this->errors[] = "Critical Error: " . $e->getMessage();
            return null;
        }
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function startRow(): int
    {
        return 2;
    }
}
