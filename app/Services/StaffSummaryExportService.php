<?php

namespace App\Services;

use App\Support\MediaAsset;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StaffSummaryExportService
{
    private const THIN_BORDER = [
        'borderStyle' => Border::BORDER_THIN,
        'color' => ['argb' => 'FF888888'],
    ];

    /**
     * Export staff summary into one worksheet that mirrors the printed matrix layout.
     */
    public function export(
        Collection $regularStaffRows,
        Collection $emergencyLaborerRows,
        array $preparedBy,
        array $notedBy,
        array $meta = []
    ): string {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Staff Summary');

        $this->configureSheet($sheet);
        $currentRow = $this->renderHeader($sheet, $meta);

        $currentRow = $this->renderSection(
            $sheet,
            $currentRow,
            'STAFF (PERMANENT, CASUAL AND CONTRACTUAL)',
            $regularStaffRows,
            false
        );

        $currentRow += 1;

        $currentRow = $this->renderSection(
            $sheet,
            $currentRow,
            'EMERGENCY LABORER',
            $emergencyLaborerRows,
            true
        );

        $this->renderSignatures($sheet, $currentRow + 2, $preparedBy, $notedBy);

        $outputDir = storage_path('app/exports');
        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $fileName = 'Staff_Summary_'.now()->format('Ymd_His').'.xlsx';
        $filePath = $outputDir.DIRECTORY_SEPARATOR.$fileName;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }

    private function renderSection(
        Worksheet $sheet,
        int $startRow,
        string $sectionTitle,
        Collection $members,
        bool $numberRows
    ): int {
        $currentRow = $startRow;
        $sheet->mergeCells("A{$currentRow}:F{$currentRow}");
        $sheet->setCellValue("A{$currentRow}", $sectionTitle);
        $sheet->getStyle("A{$currentRow}:F{$currentRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 13],
            'borders' => ['allBorders' => self::THIN_BORDER],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension($currentRow)->setRowHeight(24);
        $currentRow++;

        $headers = ['Name', 'Position', 'Status', 'Office Assignment', 'Rating', 'Adjectival Rating'];
        $columns = ['A', 'B', 'C', 'D', 'E', 'F'];

        foreach ($headers as $idx => $header) {
            $sheet->setCellValue($columns[$idx].$currentRow, $header);
        }

        $sheet->getStyle("A{$currentRow}:F{$currentRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'borders' => ['allBorders' => self::THIN_BORDER],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension($currentRow)->setRowHeight(24);
        $currentRow++;

        $rowsToRender = $members->count();

        if ($rowsToRender === 0) {
            $sheet->mergeCells("A{$currentRow}:F{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", 'No records found.');
            $sheet->getStyle("A{$currentRow}:F{$currentRow}")->applyFromArray([
                'font' => ['italic' => true],
                'borders' => ['allBorders' => self::THIN_BORDER],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            $sheet->getRowDimension($currentRow)->setRowHeight(24);

            return $currentRow + 1;
        }

        for ($i = 0; $i < $rowsToRender; $i++) {
            $member = $members->get($i);

            $name = '';
            $position = '';
            $status = '';
            $office = '';
            $rating = null;
            $adjectival = '';

            if ($member) {
                $name = strtoupper((string) ($member->name ?? ''));
                $position = (string) ($member->designation->title ?? '');
                $status = (string) ($member->employment_status ?? '');
                $office = (string) ($member->department->name ?? '');
                $rating = $member->calibrated_rating !== null ? (float) $member->calibrated_rating : null;
                $adjectival = (string) ($member->adjectival_rating ?? '');
            }

            if ($numberRows) {
                $name = ($i + 1).($name !== '' ? '. '.$name : '');
            }

            $sheet->setCellValue("A{$currentRow}", $name);
            $sheet->setCellValue("B{$currentRow}", $position);
            $sheet->setCellValue("C{$currentRow}", $status);
            $sheet->setCellValue("D{$currentRow}", $office);
            $sheet->setCellValue("E{$currentRow}", $rating !== null ? round($rating, 2) : '');
            $sheet->setCellValue("F{$currentRow}", $adjectival);

            $sheet->getStyle("A{$currentRow}:F{$currentRow}")->applyFromArray([
                'borders' => ['allBorders' => self::THIN_BORDER],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ]);

            $sheet->getStyle("A{$currentRow}:D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("E{$currentRow}:F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$currentRow}")->getNumberFormat()->setFormatCode('0.00');
            $sheet->getRowDimension($currentRow)->setRowHeight(24);

            $currentRow++;
        }

        return $currentRow;
    }

    private function configureSheet(Worksheet $sheet): void
    {
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(11);

        $sheet->getColumnDimension('A')->setWidth(38);
        $sheet->getColumnDimension('B')->setWidth(28);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(24);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(24);

        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToWidth(1)
            ->setFitToHeight(0);

        $sheet->getPageMargins()->setTop(0.35)->setBottom(0.35)->setLeft(0.25)->setRight(0.25);
    }

    private function renderHeader(Worksheet $sheet, array $meta): int
    {
        $campus = strtoupper((string) ($meta['campus'] ?? 'BINANGONAN'));

        $sheet->mergeCells('B1:E1');
        $sheet->mergeCells('B2:E2');
        $sheet->mergeCells('B3:E3');
        $sheet->mergeCells('A5:F5');
        $sheet->mergeCells('A7:F7');

        $sheet->setCellValue('B1', 'Republic of the Philippines');
        $sheet->setCellValue('B2', 'University of Rizal System');
        $sheet->setCellValue('B3', 'Province of Rizal');
        $sheet->setCellValue('A5', 'Individual Performance Commitment and Review (IPCR)');
        $sheet->setCellValue('A7', $campus);

        $sheet->getStyle('B1:E3')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => ['size' => 12],
        ]);
        $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(15);

        $sheet->getStyle('A5:A7')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A7')->getFont()->setSize(16);

        $sheet->getRowDimension(1)->setRowHeight(20);
        $sheet->getRowDimension(2)->setRowHeight(24);
        $sheet->getRowDimension(3)->setRowHeight(22);
        $sheet->getRowDimension(5)->setRowHeight(30);
        $sheet->getRowDimension(7)->setRowHeight(28);

        $logoPath = MediaAsset::publicImageLocalPath('urs_logo.jpg');
        if ($logoPath && is_file($logoPath)) {
            $drawing = new Drawing;
            $drawing->setName('URS Logo');
            $drawing->setDescription('URS Logo');
            $drawing->setPath($logoPath);
            $drawing->setHeight(86);
            $drawing->setCoordinates('A1');
            $drawing->setOffsetX(12);
            $drawing->setOffsetY(3);
            $drawing->setWorksheet($sheet);
        }

        return 9;
    }

    private function renderSignatures(Worksheet $sheet, int $startRow, array $preparedBy, array $notedBy): void
    {
        $signatureLabelRow = $startRow;

        $sheet->setCellValue("A{$signatureLabelRow}", 'Prepared by:');
        $sheet->setCellValue("D{$signatureLabelRow}", 'Noted by:');
        $sheet->getStyle("A{$signatureLabelRow}:F{$signatureLabelRow}")->getFont()->setSize(12);

        $nameRow = $signatureLabelRow + 2;

        $preparedName = strtoupper(trim((string) ($preparedBy['name'] ?? '')));
        $preparedPosition = trim((string) ($preparedBy['position'] ?? ''));

        $notedName = strtoupper(trim((string) ($notedBy['name'] ?? '')));
        $notedPosition = trim((string) ($notedBy['position'] ?? ''));

        $sheet->mergeCells("A{$nameRow}:B{$nameRow}");
        $sheet->mergeCells('A'.($nameRow + 1).':B'.($nameRow + 1));
        $sheet->mergeCells("D{$nameRow}:E{$nameRow}");
        $sheet->mergeCells('D'.($nameRow + 1).':E'.($nameRow + 1));

        $sheet->setCellValue("A{$nameRow}", $preparedName);
        $sheet->setCellValue('A'.($nameRow + 1), $preparedPosition !== '' ? $preparedPosition : 'HRMO');

        $sheet->setCellValue("D{$nameRow}", $notedName);
        $sheet->setCellValue('D'.($nameRow + 1), $notedPosition !== '' ? $notedPosition : 'Campus Director');

        $sheet->getStyle("A{$nameRow}:B{$nameRow}")->getFont()->setBold(true)->setUnderline(true)->setSize(12);
        $sheet->getStyle("D{$nameRow}:E{$nameRow}")->getFont()->setBold(true)->setUnderline(true)->setSize(12);

        $sheet->getStyle('A'.($nameRow + 1).':B'.($nameRow + 1))->getFont()->setSize(11);
        $sheet->getStyle('D'.($nameRow + 1).':E'.($nameRow + 1))->getFont()->setSize(11);

        $sheet->getStyle("A{$nameRow}:B".($nameRow + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("D{$nameRow}:E".($nameRow + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }
}
