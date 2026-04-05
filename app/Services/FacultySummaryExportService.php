<?php

namespace App\Services;

use App\Support\MediaAsset;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FacultySummaryExportService
{
    private const THIN_BORDER = [
        'borderStyle' => Border::BORDER_THIN,
        'color' => ['argb' => 'FF888888'],
    ];

    /**
     * Export faculty summary rows into XLSX using an Excel-like matrix layout.
     */
    public function export(Collection $departmentRows, array $preparedBy, array $approvedBy, array $meta = []): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Faculty Summary');

        $this->configureSheet($sheet);
        $currentRow = $this->renderHeader($sheet, $meta);

        $accentPalette = [
            'FFC6E0B4', // green
            'FFD9C2E9', // violet
            'FFFFFF00', // yellow
            'FFB4C6E7', // blue
            'FFF8CBAD', // peach
            'FFC9DAF8', // soft blue
        ];

        foreach ($departmentRows->values() as $index => $block) {
            $accentColor = $accentPalette[$index % count($accentPalette)];
            $currentRow = $this->renderDepartmentBlock($sheet, $currentRow, $block, $accentColor);
        }

        if ($departmentRows->isEmpty()) {
            $sheet->mergeCells("A{$currentRow}:F{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", 'No faculty records found for the selected department.');
            $sheet->getStyle("A{$currentRow}:F{$currentRow}")->applyFromArray([
                'font' => ['italic' => true],
                'borders' => ['allBorders' => self::THIN_BORDER],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            $sheet->getRowDimension($currentRow)->setRowHeight(22);
            $currentRow++;
        }

        $this->renderSignatures($sheet, $currentRow + 2, $preparedBy, $approvedBy);

        $outputDir = storage_path('app/exports');
        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $fileName = 'Faculty_Summary_'.now()->format('Ymd_His').'.xlsx';
        $filePath = $outputDir.DIRECTORY_SEPARATOR.$fileName;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }

    private function configureSheet(Worksheet $sheet): void
    {
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(11);

        $sheet->getColumnDimension('A')->setWidth(34);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(22);

        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToWidth(1)
            ->setFitToHeight(0);

        $sheet->getPageMargins()->setTop(0.35)->setBottom(0.35)->setLeft(0.25)->setRight(0.25);
    }

    private function renderHeader(Worksheet $sheet, array $meta): int
    {
        $campus = strtoupper((string) ($meta['campus'] ?? 'BINANGONAN'));
        $generatedAt = $meta['generated_at'] ?? now();

        $sheet->mergeCells('B1:E1');
        $sheet->mergeCells('B2:E2');
        $sheet->mergeCells('B3:E3');
        $sheet->mergeCells('B4:E4');

        $sheet->setCellValue('B1', 'Republic of the Philippines');
        $sheet->setCellValue('B2', 'University of Rizal System');
        $sheet->setCellValue('B3', 'Province of Rizal');
        $sheet->setCellValue('B4', $campus);

        $sheet->mergeCells('A6:F6');
        $sheet->mergeCells('A7:F7');
        $sheet->setCellValue('A6', 'Individual Performance Commitment and Review (IPCR)');
        $sheet->setCellValue('A7', 'Faculty Summary Report • Generated '.$generatedAt->format('F d, Y h:i A'));

        $sheet->getStyle('B1:E4')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'size' => 12,
            ],
        ]);

        $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A6:A7')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getStyle('A6')->getFont()->setSize(14);
        $sheet->getStyle('A7')->getFont()->setSize(11)->setBold(false);

        $sheet->getRowDimension(1)->setRowHeight(18);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(18);
        $sheet->getRowDimension(4)->setRowHeight(18);
        $sheet->getRowDimension(6)->setRowHeight(22);
        $sheet->getRowDimension(7)->setRowHeight(20);

        $logoPath = MediaAsset::publicImageLocalPath('urs_logo.jpg');
        if ($logoPath && is_file($logoPath)) {
            $drawing = new Drawing;
            $drawing->setName('URS Logo');
            $drawing->setDescription('URS Logo');
            $drawing->setPath($logoPath);
            $drawing->setHeight(72);
            $drawing->setCoordinates('A1');
            $drawing->setOffsetX(6);
            $drawing->setOffsetY(2);
            $drawing->setWorksheet($sheet);
        }

        return 9;
    }

    private function renderDepartmentBlock(Worksheet $sheet, int $startRow, $block, string $accentColor): int
    {
        $department = $block['department'] ?? null;
        $permanentMembers = collect($block['permanent'] ?? []);
        $partTimeMembers = collect($block['part_time'] ?? []);
        $departmentName = strtoupper((string) ($department->name ?? 'UNASSIGNED DEPARTMENT'));

        $sheet->mergeCells("A{$startRow}:F{$startRow}");
        $sheet->setCellValue("A{$startRow}", $departmentName);
        $sheet->getStyle("A{$startRow}:F{$startRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF2F2F2'],
            ],
            'borders' => ['allBorders' => self::THIN_BORDER],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension($startRow)->setRowHeight(22);
        $startRow++;

        if ($permanentMembers->isEmpty() && $partTimeMembers->isEmpty()) {
            $sheet->mergeCells("A{$startRow}:F{$startRow}");
            $sheet->setCellValue("A{$startRow}", 'No records found in this department.');
            $sheet->getStyle("A{$startRow}:F{$startRow}")->applyFromArray([
                'font' => ['italic' => true],
                'borders' => ['allBorders' => self::THIN_BORDER],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            $sheet->getRowDimension($startRow)->setRowHeight(22);

            return $startRow + 1;
        }

        if ($permanentMembers->isNotEmpty()) {
            $startRow = $this->renderEmployeeGroup(
                $sheet,
                $startRow,
                'PERMANENT FACULTY',
                $permanentMembers,
                $accentColor,
                false
            );
        }

        if ($partTimeMembers->isNotEmpty()) {
            $startRow = $this->renderEmployeeGroup(
                $sheet,
                $startRow,
                'PART-TIME FACULTY',
                $partTimeMembers,
                $accentColor,
                true
            );
        }

        return $startRow;
    }

    private function renderEmployeeGroup(
        Worksheet $sheet,
        int $startRow,
        string $groupTitle,
        Collection $members,
        string $accentColor,
        bool $isPartTime
    ): int {
        $sheet->mergeCells("A{$startRow}:F{$startRow}");
        $sheet->setCellValue("A{$startRow}", $groupTitle);
        $sheet->getStyle("A{$startRow}:F{$startRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF9F9F9'],
            ],
            'borders' => ['allBorders' => self::THIN_BORDER],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension($startRow)->setRowHeight(20);
        $startRow++;

        $headers = ['Name', 'Position', 'Status', 'Office Assignment', 'Rating', 'Adjectival Rating'];
        $columnKeys = ['A', 'B', 'C', 'D', 'E', 'F'];

        foreach ($headers as $index => $headerLabel) {
            $cell = $columnKeys[$index].$startRow;
            $sheet->setCellValue($cell, $headerLabel);
        }

        $sheet->getStyle("A{$startRow}:F{$startRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF2F2F2'],
            ],
            'borders' => ['allBorders' => self::THIN_BORDER],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension($startRow)->setRowHeight(22);
        $startRow++;

        foreach ($members->values() as $member) {
            $status = $isPartTime
                ? 'Part Time'
                : ((string) ($member->employment_status ?? '') !== '' ? $member->employment_status : 'Permanent');

            $sheet->setCellValue("A{$startRow}", strtoupper((string) ($member->name ?? '')));
            $sheet->setCellValue("B{$startRow}", (string) ($member->designation->title ?? ''));
            $sheet->setCellValue("C{$startRow}", $status);
            $sheet->setCellValue("D{$startRow}", (string) ($member->department->name ?? ''));

            $rating = $member->calibrated_rating;
            if ($rating !== null) {
                $sheet->setCellValue("E{$startRow}", round((float) $rating, 2));
            } else {
                $sheet->setCellValue("E{$startRow}", '');
            }

            $sheet->setCellValue("F{$startRow}", (string) ($member->adjectival_rating ?? ''));

            $sheet->getStyle("A{$startRow}:F{$startRow}")->applyFromArray([
                'borders' => ['allBorders' => self::THIN_BORDER],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ]);

            $sheet->getStyle("A{$startRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($accentColor);
            $sheet->getStyle("E{$startRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($accentColor);

            $sheet->getStyle("A{$startRow}:D{$startRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("E{$startRow}:F{$startRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$startRow}")->getNumberFormat()->setFormatCode('0.00');

            $sheet->getRowDimension($startRow)->setRowHeight(22);
            $startRow++;
        }

        return $startRow;
    }

    private function renderSignatures(Worksheet $sheet, int $startRow, array $preparedBy, array $approvedBy): void
    {
        $signatureRow = $startRow;

        $sheet->setCellValue("A{$signatureRow}", 'Prepared by:');
        $sheet->setCellValue("D{$signatureRow}", 'Approved by:');
        $sheet->getStyle("A{$signatureRow}:F{$signatureRow}")->getFont()->setSize(12);

        $nameRow = $signatureRow + 2;

        $preparedName = strtoupper(trim((string) ($preparedBy['name'] ?? '')));
        $preparedPosition = trim((string) ($preparedBy['position'] ?? ''));

        $approvedName = strtoupper(trim((string) ($approvedBy['name'] ?? '')));
        $approvedPosition = trim((string) ($approvedBy['position'] ?? ''));

        $sheet->mergeCells("A{$nameRow}:B{$nameRow}");
        $sheet->mergeCells('A'.($nameRow + 1).':B'.($nameRow + 1));
        $sheet->mergeCells("D{$nameRow}:E{$nameRow}");
        $sheet->mergeCells('D'.($nameRow + 1).':E'.($nameRow + 1));

        $sheet->setCellValue("A{$nameRow}", $preparedName);
        $sheet->setCellValue('A'.($nameRow + 1), $preparedPosition !== '' ? $preparedPosition : 'HRMO');

        $sheet->setCellValue("D{$nameRow}", $approvedName);
        $sheet->setCellValue('D'.($nameRow + 1), $approvedPosition !== '' ? $approvedPosition : 'Campus Director');

        $sheet->getStyle("A{$nameRow}:B{$nameRow}")->getFont()->setBold(true)->setUnderline(true)->setSize(12);
        $sheet->getStyle("D{$nameRow}:E{$nameRow}")->getFont()->setBold(true)->setUnderline(true)->setSize(12);

        $sheet->getStyle('A'.($nameRow + 1).':B'.($nameRow + 1))->getFont()->setSize(11);
        $sheet->getStyle('D'.($nameRow + 1).':E'.($nameRow + 1))->getFont()->setSize(11);

        $sheet->getStyle("A{$nameRow}:B".($nameRow + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("D{$nameRow}:E".($nameRow + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }
}
