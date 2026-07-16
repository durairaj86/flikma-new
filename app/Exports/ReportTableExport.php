<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Generic report sheet: title + info lines, a dynamic set of columns,
 * data rows and a totals footer. Used by the aging reports (whose bucket
 * columns depend on the user-selected interval/column count) and the
 * supplier statement.
 */
class ReportTableExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected array $rows;
    protected array $totalsRow;
    protected array $columns;
    protected array $meta;
    protected int $headerRowIndex;

    public function __construct(array $rows, array $totalsRow, array $columns, array $meta)
    {
        $this->rows = $rows;
        $this->totalsRow = $totalsRow;
        $this->columns = $columns;
        $this->meta = $meta;
        $this->headerRowIndex = 2 + count($meta['lines'] ?? []) + 1; // title + lines + spacer + header
    }

    public function collection()
    {
        $rows = collect($this->rows);
        $rows->push($this->totalsRow);

        return $rows;
    }

    public function headings(): array
    {
        $headings = [[$this->meta['title'] ?? 'AGING REPORT']];

        foreach ($this->meta['lines'] ?? [] as $line) {
            $headings[] = [$line];
        }

        $headings[] = [];
        $headings[] = $this->columns;

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            $this->headerRowIndex => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
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
                $lastCol = Coordinate::stringFromColumnIndex(count($this->columns));

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                foreach (range(1, count($this->columns)) as $i) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
                }

                $footerRange = "A$highestRow:$lastCol$highestRow";
                $sheet->getStyle($footerRange)->getFont()->setBold(true);
                $sheet->getStyle($footerRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E9ECEF');

                $numericFrom = $this->meta['numeric_from'] ?? 1;
                $firstNumericCol = Coordinate::stringFromColumnIndex($numericFrom);
                $firstDataRow = $this->headerRowIndex + 1;
                $sheet->getStyle("$firstNumericCol$firstDataRow:$lastCol$highestRow")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            },
        ];
    }
}
