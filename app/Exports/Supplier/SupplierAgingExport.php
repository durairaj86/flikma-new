<?php

namespace App\Exports\Supplier;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SupplierAgingExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected $data;
    protected $totals;
    protected $supplierName;

    public function __construct($data, $totals, $supplierName)
    {
        $this->data = $data;
        $this->totals = $totals;
        $this->supplierName = $supplierName;
    }

    public function collection()
    {
        $rows = collect($this->data)->map(function ($item) {
            return [
                $item['invoice_no'],
                $item['invoice_date'],
                $item['due_date'],
                $item['current'],
                $item['days_1_30'],
                $item['days_31_60'],
                $item['days_61_90'],
                $item['days_91_120'],
                $item['days_over_120'],
                $item['total'],
            ];
        });

        // Add TOTALS Row at the end
        $rows->push([
            '',     // A
            '',     // B
            'TOTALS', // C
            $this->totals['current'],  // D
            $this->totals['days_1_30'],  // E
            $this->totals['days_31_60'],  // F
            $this->totals['days_61_90'],  // G
            $this->totals['days_91_120'],  // H
            $this->totals['days_over_120'],  // I
            $this->totals['grand_total']  // J
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            ['SUPPLIER AGING REPORT'], // Row 1: Main Title
            ['Generated on: ' . now()->format('d-m-Y H:i')], // Row 2: Date
            ['Supplier: ' . $this->supplierName], // Row 3: Supplier Name
            [], // Row 4: Spacer
            ['Invoice No', 'Invoice Date', 'Due Date', 'Current', '1-30 Days', '31-60 Days', '61-90 Days', '91-120 Days', 'Over 120 Days', 'Total'] // Row 5: Table Headers
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the main title
            1 => ['font' => ['bold' => true, 'size' => 16]],
            // Style the supplier name
            3 => ['font' => ['bold' => true]],
            // Style the table headers (Row 5)
            5 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $lastCol = 'J';

                // 1. Merge Title Cells
                $sheet->mergeCells('A1:F1');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // 2. Auto-size columns
                foreach (range('A', 'J') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // 3. Style the TOTALS Row (The last row)
                $footerRange = "A$highestRow:" . $lastCol . $highestRow;
                $sheet->getStyle($footerRange)->getFont()->setBold(true);
                $sheet->getStyle($footerRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E9ECEF');

                // 4. Format Currency Columns (D-J)
                $currencyRange = "D6:J$highestRow";
                $sheet->getStyle($currencyRange)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            },
        ];
    }
}
