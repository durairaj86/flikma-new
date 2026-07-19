<?php

namespace App\Exports\Customer;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerStatementReportExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected $transactions;
    protected $summary;

    public function __construct($transactions, $summary)
    {
        $this->transactions = $transactions;
        $this->summary = $summary;
    }

    public function collection()
    {
        $running = (float)($this->summary['opening'] ?? 0);
        $baseCurrency = $this->summary['base_currency'] ?? 'SAR';

        $rows = collect([[
            '', 'Balance Brought Forward', '', '', '', '', '', number_format($running, 2, '.', ''),
        ]]);

        foreach ($this->transactions as $txn) {
            $running += (float)$txn->debit - (float)$txn->credit;
            $fcy = ($txn->fcy_amount ?? null) !== null
                ? $txn->currency . ' ' . number_format($txn->fcy_amount, 2, '.', '') . ' (' . $baseCurrency . ' ' . number_format($txn->currency_rate, 4, '.', '') . ')'
                : '';
            $date = $txn->display_date;
            if (($txn->days_overdue ?? 0) > 0) {
                $date .= "\nOverdue " . $txn->days_overdue . ' ' . ($txn->days_overdue == 1 ? 'day' : 'days');
            }
            $rows->push([
                $date,
                $txn->reference,
                strtoupper($txn->type),
                $txn->description,
                $fcy,
                (float)$txn->debit ?: '',
                (float)$txn->credit ?: '',
                $running,
            ]);
        }

        $rows->push([
            '', '', '', 'CLOSING TOTALS', '',
            (float)($this->summary['total_debit'] ?? 0),
            (float)($this->summary['total_credit'] ?? 0),
            (float)($this->summary['closing'] ?? 0),
        ]);

        return $rows;
    }

    public function headings(): array
    {
        $period = Carbon::parse($this->summary['start_date'])->format('d-m-Y')
            . ' to ' . Carbon::parse($this->summary['end_date'])->format('d-m-Y');

        return [
            ['CUSTOMER STATEMENT'],
            ['Statement Period: ' . $period],
            ['Generated on: ' . now()->format('d-m-Y H:i')],
            [],
            ['Customer Name:', $this->summary['name'], '', 'Opening Balance:', (float)($this->summary['opening'] ?? 0)],
            ['Customer Code:', $this->summary['customer_code'], '', 'Closing Balance:', (float)($this->summary['closing'] ?? 0)],
            [],
            ['Date', 'Reference', 'Type', 'Description', 'FCY Amount', 'Debit', 'Credit', 'Balance'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            8 => [
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

                $sheet->mergeCells('A1:H1');
                $sheet->mergeCells('A2:H2');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A2')->getFont()->setBold(true);

                $sheet->getStyle('A5:A6')->getFont()->setBold(true);
                $sheet->getStyle('D5:D6')->getFont()->setBold(true);

                foreach (range('A', 'H') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Date column may carry a second "Overdue N days" line —
                // wrap so it renders as two lines instead of one long string,
                // and color just that line red (rich text — a plain cell
                // style would color the date too).
                $sheet->getStyle("A9:A$highestRow")->getAlignment()->setWrapText(true);

                for ($row = 9; $row <= $highestRow; $row++) {
                    $cell = $sheet->getCell("A$row");
                    $value = $cell->getValue();

                    if (is_string($value) && str_contains($value, "\nOverdue")) {
                        [$datePart, $overduePart] = explode("\n", $value, 2);

                        $richText = new RichText();
                        $richText->createText($datePart . "\n");

                        $overdueRun = $richText->createTextRun($overduePart);
                        $overdueRun->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFDC3545'));
                        $overdueRun->getFont()->setBold(true);

                        $cell->setValue($richText);
                    }
                }

                // Opening balance row and closing totals row
                $sheet->getStyle('A9:H9')->getFont()->setBold(true);
                $footerRange = "A$highestRow:H$highestRow";
                $sheet->getStyle($footerRange)->getFont()->setBold(true);
                $sheet->getStyle($footerRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E9ECEF');

                $sheet->getStyle("F9:H$highestRow")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            },
        ];
    }
}
