<?php

namespace App\Exports;

use App\Models\Crm\CrmAction;
use App\Services\Crm\CrmSettings;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * The CRM Dashboard as an Excel sheet laid out like the sales workbook's Dashboard sheet ("SALES DASHBOARD - where do I stand?"):
 * the title bar, viewing date, AT A GLANCE tiles, and two blocks side by side. Left (B-K): activity snapshot, action detail,
 * conversion. Right (M-R): pipeline by status, by rank, by pricing executive, by service type. Colours, widths and number formats
 * are the workbook's. Values, not formulas: the sheet is a snapshot of the dashboard on screen.
 */
class CrmDashboardExport
{
    private const NAVY = '1F3864';
    private const BLUE = '2F5597';
    private const COUNT = '#,##0;-#,##0;"-"';
    private const MONEY = '#,##0;(#,##0);"-"';
    private const PCT = '0.0%';

    private Worksheet $ws;

    /** $data: the same arrays the dashboard page gets (CrmDashboardController). */
    public function __construct(private array $data, private string $company, private string $currency, private string $who)
    {
    }

    public function download(string $filename)
    {
        $book = new Spreadsheet();
        $this->ws = $book->getActiveSheet();
        $this->ws->setTitle('Dashboard');
        $this->build();

        return response()->streamDownload(function () use ($book) {
            (new Xlsx($book))->setIncludeCharts(true)->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    private function build(): void
    {
        $ws = $this->ws;
        $ws->setShowGridlines(false);
        $ws->getSheetView()->setZoomScale(90);
        foreach (['A' => 2, 'B' => 17, 'C' => 22, 'L' => 3, 'M' => 26] + array_fill_keys(range('D', 'K'), 12.5) + array_fill_keys(range('N', 'R'), 13) as $col => $w) {
            $ws->getColumnDimension($col)->setWidth($w);
        }
        $ws->getParent()->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);

        // title bar
        $ws->setCellValue('B1', __('SALES DASHBOARD  -  where do I stand?'));
        $this->fill('A1:R1', self::NAVY, 'FFFFFF', true, 16);
        $ws->getRowDimension(1)->setRowHeight(32);
        $ws->getStyle('A1:R1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // viewing date, company, whose numbers
        /** @var Carbon $asOf */
        $asOf = $this->data['asOf'];
        $ws->setCellValue('B3', __('Company:'))->setCellValue('D3', $this->company);
        $ws->setCellValue('B4', __('Viewing date:'));
        $ws->setCellValue('D4', \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($asOf->copy()->startOfDay()));
        $ws->getStyle('D4')->getNumberFormat()->setFormatCode('dd-mmm-yyyy');
        $this->fill('D4', 'DDEBF7', self::NAVY, true);
        $ws->setCellValue('E4', $asOf->translatedFormat('l'));
        if (!$this->data['isToday']) {
            $ws->setCellValue('G4', __('Viewing another date'));
            $ws->getStyle('G4')->getFont()->setBold(true)->getColor()->setRGB('C00000');
        }
        $ws->setCellValue('M4', __('Salesperson:'))->setCellValue('N4', $this->who);
        $ws->setCellValue('M3', __('Exported:'))->setCellValue('N3', now()->format('d-m-Y H:i'));
        $ws->getStyle('B3:B4')->getFont()->setBold(true);
        $ws->getStyle('M3:M4')->getFont()->setBold(true);

        // AT A GLANCE
        $ws->mergeCells('B6:C7');
        $ws->setCellValue('B6', __('AT A GLANCE') . "\n(" . __('viewing date') . ')');
        $this->fill('B6:C7', self::BLUE, 'FFFFFF', true, 11);
        $ws->getStyle('B6')->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $g = $this->data['glance'];
        $tiles = [
            ['D', __('Customer lines'), $g['lines'], 'EAF1FB'],
            ['E', __('Jobs running'), $g['jobs_running'], 'EAF1FB'],
            ['F', __('RFQs received (this month)'), $g['rfqs'], 'FDF1E3'],
            ['G', __('Quotes sent (this month)'), $g['quotes'], 'FDF1E3'],
            ['H', __('Confirmed (this month)'), $g['confirmed'], 'E8F3E2'],
            ['I', __('Overdue: planned, not updated'), $g['overdue'], 'FDE7E9'],
            ['J', __('Planned next 7 days'), $g['next7'], 'FFF9E5'],
            ['K', __('No contact :n+ days', ['n' => CrmSettings::get('stale_days')]), $g['stale'], 'FDE7E9'],
        ];
        foreach ($tiles as [$c, $label, $value, $bg]) {
            $ws->setCellValue("{$c}6", $label)->setCellValue("{$c}7", $value);
            $this->fill("{$c}6", $bg, '404040', true, 8);
            $this->fill("{$c}7", $bg, self::NAVY, true, 18);
            $ws->getStyle("{$c}6:{$c}7")->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $ws->getStyle("{$c}7")->getNumberFormat()->setFormatCode('#,##0');
        }
        $ws->getRowDimension(6)->setRowHeight(34);
        $ws->getRowDimension(7)->setRowHeight(34);
        $this->border('D6:K7');

        $left = $this->left(9);
        $right = $this->right(9);
        $end = $this->trends(max($left, $right) + 1);
        $ws->freezePane('A5');
        $ws->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToWidth(1)->setFitToHeight(0)->setPrintArea('A1:R' . $end);
        $ws->getParent()->setActiveSheetIndex(0);
    }

    /**
     * TRENDS (the workbook's two charts): weekly outreach / RFQs & quotes / wins as lines, monthly activity by category as stacked
     * columns. The charts read the Weekly and Monthly sheets added after the dashboard, as in the workbook. Returns the last row.
     */
    private function trends(int $row): int
    {
        $this->section("B$row", __('TRENDS'));
        $weekly = $this->periodSheet('Weekly', __('WEEKLY SUMMARY  -  every action, week by week'), __('Week #'), $this->data['weekly'] ?? []);
        $monthly = $this->periodSheet('Monthly', __('MONTHLY SUMMARY  -  every action, month by month'), __('Month'), $this->data['monthly'] ?? []);
        $top = $row + 1;
        $bottom = $top + 19;

        // weekly: lines for Outreach (G), RFQ & Quote (H), Won & Running (I)
        $this->chart(\PhpOffice\PhpSpreadsheet\Chart\DataSeries::TYPE_LINECHART, \PhpOffice\PhpSpreadsheet\Chart\DataSeries::GROUPING_STANDARD,
            'Weekly', $weekly, ['G' => '5B7DB1', 'H' => 'E69138', 'I' => '6AA84F'],
            __('Weekly activity - outreach, RFQs/quotes and wins'), "B$top", "K$bottom");
        // monthly: stacked columns for the first five categories, in their colours
        $this->chart(\PhpOffice\PhpSpreadsheet\Chart\DataSeries::TYPE_BARCHART, \PhpOffice\PhpSpreadsheet\Chart\DataSeries::GROUPING_STACKED,
            'Monthly', $monthly, ['G' => 'DCE6F1', 'H' => 'F9CB9C', 'I' => 'B6D7A8', 'J' => 'A4C2F4', 'K' => 'D9D2E9'],
            __('Monthly activity by category'), "M$top", "R$bottom");

        return $bottom;
    }

    /**
     * A Weekly / Monthly sheet like the workbook's: period, from, to, total entries, other (always 0: every entry is an action from the
     * list), overdue planned, then one column per category (G onwards). Returns the number of data rows (from row 6).
     */
    private function periodSheet(string $name, string $title, string $first, array $rows): int
    {
        $book = $this->ws->getParent();
        $ws = $book->createSheet();
        $ws->setTitle($name);
        $ws->setShowGridlines(false);
        $ws->setCellValue('A1', $title);
        $ws->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB(self::NAVY);
        $heads = [$first, __('From'), __('To'), __('Total entries'), __('Other / unlisted'), __('Overdue planned')];
        foreach (CrmAction::CATEGORIES as $c) {
            $heads[] = __($c['label']);
        }
        foreach ($heads as $i => $h) {
            $ws->setCellValue(Coordinate::stringFromColumnIndex($i + 1) . '5', $h);
            $ws->getColumnDimension(Coordinate::stringFromColumnIndex($i + 1))->setWidth($i < 3 ? 13 : 12);
        }
        $lastCol = Coordinate::stringFromColumnIndex(count($heads));
        $style = $ws->getStyle("A5:{$lastCol}5");
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::NAVY);
        $style->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('FFFFFF');
        $style->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $ws->getRowDimension(5)->setRowHeight(28);
        $i = 0;
        foreach (array_values(CrmAction::CATEGORIES) as $k => $c) {
            $cell = Coordinate::stringFromColumnIndex(7 + $k) . '5';
            $ws->getStyle($cell)->getFill()->getStartColor()->setRGB(ltrim($c['bg'], '#'));
            $ws->getStyle($cell)->getFont()->getColor()->setRGB(ltrim($c['fg'], '#'));
        }
        $r = 6;
        foreach ($rows as $n => $p) {
            $ws->setCellValue("A$r", $name === 'Weekly' ? $n + 1 : $p['label']);
            // the chart's axis labels: the week's first day / the month
            $ws->setCellValue("B$r", $name === 'Weekly' ? $p['label'] : $p['from']->format('d-m-Y'));
            $ws->setCellValue("C$r", $name === 'Weekly' ? $p['to']->format('d-M') : $p['to']->format('d-m-Y'));
            $ws->setCellValue("D$r", $p['total'])->setCellValue("E$r", 0)->setCellValue("F$r", $p['overdue']);
            foreach (array_keys(CrmAction::CATEGORIES) as $k => $cat) {
                $ws->setCellValue(Coordinate::stringFromColumnIndex(7 + $k) . $r, $p['categories'][$cat] ?? 0);
            }
            $r++;
        }
        if ($rows) {
            $ws->getStyle("D6:{$lastCol}" . ($r - 1))->getNumberFormat()->setFormatCode(self::COUNT);
            $ws->getStyle("A5:{$lastCol}" . ($r - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D0D0D0');
        }
        $ws->freezePane('A6');
        $ws->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)->setFitToWidth(1)->setFitToHeight(0);

        return count($rows);
    }

    /** One chart on the dashboard, its series read from the Weekly / Monthly sheet (category labels in A for months, B for weeks). */
    private function chart(string $type, string $grouping, string $sheet, int $count, array $series, string $title, string $from, string $to): void
    {
        if (!$count) {
            return;
        }
        $last = 5 + $count;
        $labelCol = $sheet === 'Weekly' ? 'B' : 'A';
        $labels = $values = [];
        foreach ($series as $col => $color) {
            $labels[] = new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', "'$sheet'!\${$col}\$5", null, 1);
            $v = new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('Number', "'$sheet'!\${$col}\$6:\${$col}\$$last", null, $count);
            if ($type === \PhpOffice\PhpSpreadsheet\Chart\DataSeries::TYPE_LINECHART) {
                $v->setLineColorProperties($color);
                $v->setLineWidth(1.75);          // points (the workbook's lines are 22000 EMU = 1.73 pt)
                $v->setPointMarker('none');
            } else {
                $v->setFillColor($color);
            }
            $values[] = $v;
        }
        $categories = [new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', "'$sheet'!\${$labelCol}\$6:\${$labelCol}\$$last", null, $count)];
        $ds = new \PhpOffice\PhpSpreadsheet\Chart\DataSeries($type, $grouping, range(0, count($values) - 1), $labels, $categories, $values);
        if ($type === \PhpOffice\PhpSpreadsheet\Chart\DataSeries::TYPE_BARCHART) {
            $ds->setPlotDirection(\PhpOffice\PhpSpreadsheet\Chart\DataSeries::DIRECTION_COL);
        }
        $chart = new \PhpOffice\PhpSpreadsheet\Chart\Chart(
            strtolower($sheet) . '_trend',
            new \PhpOffice\PhpSpreadsheet\Chart\Title($title),
            new \PhpOffice\PhpSpreadsheet\Chart\Legend(\PhpOffice\PhpSpreadsheet\Chart\Legend::POSITION_BOTTOM, null, false),
            new \PhpOffice\PhpSpreadsheet\Chart\PlotArea(null, [$ds]),
            true, \PhpOffice\PhpSpreadsheet\Chart\DataSeries::EMPTY_AS_GAP, null,
            new \PhpOffice\PhpSpreadsheet\Chart\Title(__('Entries'))
        );
        $chart->setTopLeftPosition($from);
        $chart->setBottomRightPosition($to);
        $this->ws->addChart($chart);
    }

    /** Left block: activity snapshot, action detail, conversion. Returns the last row used. */
    private function left(int $row): int
    {
        $ws = $this->ws;
        $periods = $this->data['periods'];
        $cols = array_slice(range('D', 'K'), 0, count($periods));
        $snap = $this->data['snapshot'];

        // ACTIVITY SNAPSHOT
        $this->section("B$row", __('ACTIVITY SNAPSHOT  -  how many of each action'));
        $row++;
        $this->header($row, ['B' => __('Category'), 'C' => __('Period')] + array_combine($cols, array_column(array_values($periods), 0)));
        $row++;
        foreach ([__('from') => 1, __('to') => 2] as $label => $i) {
            $ws->setCellValue("C$row", $label);
            $this->fill("B$row", self::BLUE, 'FFFFFF', true, 9);
            foreach (array_values($periods) as $k => $p) {
                $ws->setCellValue($cols[$k] . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($p[$i]->copy()->startOfDay()));
            }
            $ws->getStyle("D$row:" . end($cols) . $row)->getNumberFormat()->setFormatCode('dd-mmm-yy');
            $ws->getStyle("C$row:" . end($cols) . $row)->getFont()->setSize(8)->getColor()->setRGB('595959');
            $ws->getStyle("D$row:" . end($cols) . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }
        $start = $row - 3;
        foreach (CrmAction::CATEGORIES as $cat => $c) {
            $ws->setCellValue("B$row", __($c['label']))->setCellValue("C$row", __('all actions'));
            foreach (array_keys($periods) as $k => $key) {
                $ws->setCellValue($cols[$k] . $row, $snap['categories'][$cat][$key] ?? 0);
            }
            $this->fill("B$row:" . end($cols) . $row, $c['bg'], $c['fg']);
            $ws->getStyle("B$row")->getFont()->setBold(true);
            $ws->getStyle("C$row")->getFont()->setSize(8);
            $ws->getStyle("D$row:" . end($cols) . $row)->getFont()->setBold(true)->getColor()->setRGB('000000');
            $row++;
        }
        $ws->setCellValue("B$row", __('ALL ENTRIES'))->setCellValue("C$row", __('logged / planned'));
        foreach (array_keys($periods) as $k => $key) {
            $ws->setCellValue($cols[$k] . $row, $snap['total'][$key] ?? 0);
        }
        $this->fill("B$row:" . end($cols) . $row, self::BLUE, 'FFFFFF', true, 9);
        $ws->getStyle("D" . ($start + 3) . ':' . end($cols) . $row)->getNumberFormat()->setFormatCode(self::COUNT);
        $this->border("B$start:" . end($cols) . $row);
        $row += 2;

        // ACTION DETAIL
        $this->section("B$row", __('ACTION DETAIL'));
        $row++;
        $this->header($row, ['B' => __('Category'), 'C' => __('Action')] + array_combine($cols, array_column(array_values($periods), 0)));
        $start = $row;
        $row++;
        foreach (CrmAction::CATEGORIES as $cat => $c) {
            foreach ($snap['actions'][$cat] ?? [] as $name => $counts) {
                $ws->setCellValue("B$row", __($c['label']))->setCellValue("C$row", $name);
                foreach (array_keys($periods) as $k => $key) {
                    $ws->setCellValue($cols[$k] . $row, $counts[$key] ?? 0);
                }
                $this->fill("B$row:C$row", $c['bg'], $c['fg']);
                $row++;
            }
        }
        $ws->getStyle("D" . ($start + 1) . ':' . end($cols) . ($row - 1))->getNumberFormat()->setFormatCode(self::COUNT);
        $this->border("B$start:" . end($cols) . ($row - 1));
        $row++;

        // CONVERSION
        $this->section("B$row", __('CONVERSION  -  RFQ to Quote to Confirmed'));
        $row++;
        $this->header($row, ['B' => __('Period'), 'C' => '', 'D' => __('RFQs received'), 'E' => __('Quotes sent'), 'F' => __('Confirmed'),
            'G' => __('RFQ > Confirmed'), 'H' => __('Quote > Confirmed')]);
        $start = $row;
        $row++;
        foreach ($this->data['conversion'] as $c) {
            $ws->setCellValue("B$row", $c['label'])->setCellValue("D$row", $c['rfqs'])->setCellValue("E$row", $c['quotes'])->setCellValue("F$row", $c['confirmed'])
                ->setCellValue("G$row", $c['rfq_rate'] === null ? '-' : $c['rfq_rate'] / 100)->setCellValue("H$row", $c['quote_rate'] === null ? '-' : $c['quote_rate'] / 100);
            $row++;
        }
        $ws->getStyle("D" . ($start + 1) . ":F" . ($row - 1))->getNumberFormat()->setFormatCode(self::COUNT);
        $ws->getStyle("G" . ($start + 1) . ":H" . ($row - 1))->getNumberFormat()->setFormatCode('0%');
        $ws->getStyle("G" . ($start + 1) . ":H" . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $this->border("B$start:H" . ($row - 1));

        return $row;
    }

    /** Right block: pipeline by status, rank, pricing executive, service type. Returns the last row used. */
    private function right(int $row): int
    {
        $cur = ' (' . $this->currency . ')';
        $blocks = [
            [__('PIPELINE BY STATUS'), __('Status'), 'byStatus',
                [[__('Customer lines'), 'lines', self::COUNT], [__('Approx cost') . $cur, 'cost', self::MONEY], [__('Approx selling') . $cur, 'selling', self::MONEY],
                    [__('Approx margin') . $cur, 'margin', self::MONEY], [__('Margin %'), 'margin_pct', self::PCT]]],
            [__('BY RANK'), __('Rank'), 'byRank',
                [[__('Customer lines'), 'lines', self::COUNT], [__('Touches'), 'touches', self::COUNT], [__('RFQs received'), 'rfqs', self::COUNT],
                    [__('Jobs confirmed'), 'jobs', self::COUNT], [__('Approx selling') . $cur, 'selling', self::MONEY]]],
            [__('BY PRICING EXECUTIVE (HANDLED BY)'), __('Handled by'), 'byHandler',
                [[__('Customer lines'), 'lines', self::COUNT], [__('Touches'), 'touches', self::COUNT], [__('RFQs received'), 'rfqs', self::COUNT],
                    [__('Jobs confirmed'), 'jobs', self::COUNT], [__('Approx selling') . $cur, 'selling', self::MONEY]]],
            [__('BY SERVICE TYPE'), __('Service type'), 'byService',
                [[__('Customer lines'), 'lines', self::COUNT], [__('RFQs received'), 'rfqs', self::COUNT], [__('Jobs confirmed'), 'jobs', self::COUNT],
                    [__('Approx selling') . $cur, 'selling', self::MONEY], [__('Approx margin') . $cur, 'margin', self::MONEY]]],
        ];
        $ws = $this->ws;
        foreach ($blocks as [$title, $first, $key, $columns]) {
            $this->section("M$row", $title);
            $row++;
            $heads = ['M' => $first];
            foreach ($columns as $i => [$label]) {
                $heads[chr(ord('N') + $i)] = $label;
            }
            $this->header($row, $heads);
            $start = $row;
            $row++;
            $rows = $this->data[$key];
            foreach ($rows as $r) {
                $ws->setCellValue("M$row", $r['label']);
                foreach ($columns as $i => [, $field]) {
                    $v = $r[$field];
                    $ws->setCellValue(chr(ord('N') + $i) . $row, $field === 'margin_pct' ? ($v === null ? '-' : $v / 100) : $v);
                }
                $row++;
            }
            // TOTAL
            $ws->setCellValue("M$row", __('TOTAL'));
            foreach ($columns as $i => [, $field]) {
                $c = chr(ord('N') + $i);
                if ($field === 'margin_pct') {
                    $pct = \App\Services\Crm\CrmDashboard::marginPct($rows);
                    $ws->setCellValue("$c$row", $pct === null ? '-' : $pct / 100);
                } else {
                    $ws->setCellValue("$c$row", array_sum(array_column($rows, $field)));
                }
            }
            $this->fill("M$row:R$row", self::BLUE, 'FFFFFF', true, 9);
            foreach ($columns as $i => [, , $fmt]) {
                $c = chr(ord('N') + $i);
                $ws->getStyle("$c" . ($start + 1) . ":$c$row")->getNumberFormat()->setFormatCode($fmt);
                $ws->getStyle("$c" . ($start + 1) . ":$c$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
            $this->border("M$start:R$row");
            $row += 2;
        }

        return $row;
    }

    private function section(string $cell, string $text): void
    {
        $this->ws->setCellValue($cell, $text);
        $this->ws->getStyle($cell)->getFont()->setBold(true)->setSize(11)->getColor()->setRGB(self::BLUE);
    }

    /** Navy header row, white bold text, centred and wrapped (the workbook's table heads). */
    private function header(int $row, array $cells): void
    {
        foreach ($cells as $col => $text) {
            $this->ws->setCellValue("$col$row", $text);
        }
        $cols = array_keys($cells);
        $range = reset($cols) . $row . ':' . end($cols) . $row;
        $this->fill($range, self::NAVY, 'FFFFFF', true, 9);
        $this->ws->getStyle($range)->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $this->ws->getRowDimension($row)->setRowHeight(28);
    }

    private function fill(string $range, string $bg, string $fg, bool $bold = false, ?int $size = null): void
    {
        // the category colours are kept as CSS (#DCE6F1); Excel wants the bare hex
        $bg = ltrim($bg, '#');
        $fg = ltrim($fg, '#');
        $style = $this->ws->getStyle($range);
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);
        $font = $style->getFont()->setBold($bold);
        $font->getColor()->setRGB($fg);
        if ($size) {
            $font->setSize($size);
        }
    }

    private function border(string $range): void
    {
        $this->ws->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D0D0D0');
    }
}
