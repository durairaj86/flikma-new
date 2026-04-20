<?php

namespace App\Import;

use App\Enums\SupplierStatusEnum;
use App\Models\Supplier\Supplier;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class SupplierImport
{
    /**
     * Get column headers from the Excel file
     *
     * @param string $path The path to the Excel file
     * @return array The column headers
     */
    public function getColumnHeaders(string $path): array
    {
        $headers = [];
        $reader = new Xlsx();
        $spreadsheet = $reader->load(Storage::path($path));
        $worksheet = $spreadsheet->getActiveSheet();

        // Get the column headers from the first row
        foreach ($worksheet->getRowIterator(1, 1) as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $cell) {
                if (!empty($cell->getValue())) {
                    $headers[] = $cell->getValue();
                }
            }
        }

        return $headers;
    }

    /**
     * Get available supplier fields for mapping
     *
     * @return array The available fields
     */
    public function getAvailableFields(): array
    {
        return [
            ['key' => 'name_en', 'label' => 'Name (English)', 'required' => 'required'],
            ['key' => 'name_ar', 'label' => 'Name (Arabic)', 'required' => 'required'],
            ['key' => 'email', 'label' => 'Email', 'required' => 'required'],
            ['key' => 'phone', 'label' => 'Phone', 'required' => 'required'],
            ['key' => 'currency', 'label' => 'Currency', 'required' => 'required'],
            ['key' => 'alt_phone', 'label' => 'Alternative Phone'],
            ['key' => 'vat_number', 'label' => 'VAT Number'],
            ['key' => 'cr_number', 'label' => 'CR Number'],
            ['key' => 'address1_en', 'label' => 'Address 1 (English)'],
            ['key' => 'address1_ar', 'label' => 'Address 1 (Arabic)'],
            ['key' => 'address2_en', 'label' => 'Address 2 (English)'],
            ['key' => 'address2_ar', 'label' => 'Address 2 (Arabic)'],
            ['key' => 'city_en', 'label' => 'City (English)'],
            ['key' => 'city_ar', 'label' => 'City (Arabic)'],
            ['key' => 'country_en', 'label' => 'Country (English)'],
            ['key' => 'country_ar', 'label' => 'Country (Arabic)'],
            ['key' => 'postal_code', 'label' => 'Postal Code'],
            ['key' => 'region', 'label' => 'Region'],
            ['key' => 'credit_limit', 'label' => 'Credit Limit'],
            ['key' => 'credit_days', 'label' => 'Credit Days'],
        ];
    }

    /**
     * Process the import with the given mapping
     *
     * @param string $path The path to the Excel file
     * @param array $mapping The column mapping
     * @return array The import results
     */
    public function process(string $path, array $mapping): array
    {
        // Check if at least one required field is mapped
        $requiredFields = ['name_en', 'name_ar', 'email', 'phone', 'currency'];
        $hasRequiredField = false;

        foreach ($mapping as $field => $columnIndex) {
            if (in_array($field, $requiredFields)) {
                $hasRequiredField = true;
                break;
            }
        }

        if (!$hasRequiredField) {
            return [
                'success' => false,
                'message' => 'You must map at least one of the following fields: Name (English), Name (Arabic), Email, Phone, or Currency.',
            ];
        }

        // Create a new instance of SupplierExcelImport with the mapping
        $importer = new SupplierExcelImport($mapping);

        // Import the Excel file using Maatwebsite/Laravel-Excel
        Excel::import($importer, Storage::path($path));

        // Get the import results
        $imported = $importer->getImportedCount();
        $errors = $importer->getErrors();

        // Return the result
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Import completed with errors.',
                'imported' => $imported,
                'errors' => $errors,
            ];
        }

        return [
            'success' => true,
            'message' => 'Import completed successfully.',
            'imported' => $imported,
        ];
    }
}
