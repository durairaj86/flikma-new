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

class SupplierStatementExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected $data;
    protected $summary;

    public function __construct($data, $summary)
    {
        $this->data = $data;
        $this->summary = $summary;
    }

    public function collection()
    {
        $rows = collect($this->data)->map(function ($item) {
            return [
                $item->reference_date,
                $item->voucher_no,
                $item->voucher_type,
                $item->reference_no,
                $item->job_number,
                $item->description,
                $item->currency,
                $item->exchange_rate,
                $item->base_debit,
                $item->base_credit,
                $item->balance,
            ];
        });

        // Add TOTALS Row at the end
        $totalDebit = collect($this->data)->sum('base_debit');
        $totalCredit = collect($this->data)->sum('base_credit');
        $finalBalance = $this->summary['closing'] ?? 0;

        $rows->push([
            '',     // A
            '',     // B
            '',     // C
            '',     // D
            '',     // E
            '',     // F
            '',     // G
            'TOTALS', // H (Description column)
            $totalDebit,  // I
            $totalCredit, // J
            $finalBalance // K
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            ['SUPPLIER STATEMENT SUMMARY'], // Row 1: Main Title
            ['Generated on: ' . now()->format('d-m-Y H:i')], // Row 2: Date
            [], // Row 3: Spacer
            ['Account Name:', $this->summary['name'], '', 'Opening Balance:', $this->summary['opening']], // Row 4
            ['Supplier Code:', $this->summary['supplier_code'], '', 'Closing Balance:', $this->summary['closing']], // Row 5
            [], // Row 6: Spacer
            ['Date', 'Voucher No', 'Voucher Type', 'Reference No', 'Job', 'Description', 'Currency', 'Exchange Rate', 'Debit', 'Credit', 'Balance'] // Row 7: Table Headers
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the main title
            1 => ['font' => ['bold' => true, 'size' => 16]],
            // Style the table headers (Row 7)
            7 => [
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
                $lastCol = 'K';

                // 1. Merge Title Cells
                $sheet->mergeCells('A1:F1');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // 2. Formatting Summary Section
                $sheet->getStyle('A4:A5')->getFont()->setBold(true);
                $sheet->getStyle('D4:D5')->getFont()->setBold(true);

                // 3. Auto-size columns
                foreach (range('A', 'K') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // 4. Style the TOTALS Row (The last row)
                $footerRange = "B$highestRow:" . $lastCol . $highestRow;
                $sheet->getStyle($footerRange)->getFont()->setBold(true);
                $sheet->getStyle($footerRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E9ECEF');

                // 5. Format Currency Columns (D, E, F)
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("D8:F$highestRow")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            },
        ];
    }
}
