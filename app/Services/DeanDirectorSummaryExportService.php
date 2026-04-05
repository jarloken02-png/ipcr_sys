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

class DeanDirectorSummaryExportService
{
    private const THIN_BORDER = [
        'borderStyle' => Border::BORDER_THIN,
        'color' => ['argb' => 'FF000000'],
    ];

    /**
     * Export dean/director summary rows into an XLSX file that mirrors the approved form layout.
     */
    public function export(Collection $rows, array $preparedBy, array $notedBy): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Dean Director Summary');

        $this->configureSheet($sheet);
        $this->renderHeader($sheet);

        $dataEndRow = $this->renderTable($sheet, $rows);
        $this->renderScaleAndSignatures($sheet, $dataEndRow + 2, $preparedBy, $notedBy);

        $outputDir = storage_path('app/exports');
        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $fileName = 'Dean_Director_Summary_'.now()->format('Ymd_His').'.xlsx';
        $filePath = $outputDir.DIRECTORY_SEPARATOR.$fileName;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }

    private function configureSheet(Worksheet $sheet): void
    {
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(11);

        $sheet->getColumnDimension('A')->setWidth(3);
        $sheet->getColumnDimension('B')->setWidth(42);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(18);
        $sheet->getColumnDimension('H')->setWidth(10);
        $sheet->getColumnDimension('I')->setWidth(12);
        $sheet->getColumnDimension('J')->setWidth(24);

        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToWidth(1)
            ->setFitToHeight(0);

        $sheet->getPageMargins()->setTop(0.35)->setBottom(0.35)->setLeft(0.25)->setRight(0.25);
    }

    private function renderHeader(Worksheet $sheet): void
    {
        // Keep header lines (rows 1-4) in the same visual position as the approved sample.
        $sheet->mergeCells('C1:H1');
        $sheet->mergeCells('C2:H2');
        $sheet->mergeCells('C3:H3');
        $sheet->mergeCells('C4:H4');

        $sheet->setCellValue('C1', 'Republic of the Philippines');
        $sheet->setCellValue('C2', 'UNIVERSITY OF RIZAL SYSTEM');
        $sheet->setCellValue('C3', 'Province of Rizal');
        $sheet->setCellValue('C4', 'BINANGONAN CAMPUS');

        $sheet->mergeCells('B6:J6');
        $sheet->mergeCells('B7:J7');
        $sheet->setCellValue('B6', 'SUMMARY OF PERFORMANCE EVALUATION');
        $sheet->setCellValue('B7', 'CAMPUS DIRECTOR AND COLLEGE DEANS');

        $sheet->getStyle('C1:H4')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'size' => 15,
            ],
        ]);

        $sheet->getStyle('C2')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('B6:B7')->applyFromArray([
            'font' => ['bold' => true, 'size' => 15],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(20);
        $sheet->getRowDimension(2)->setRowHeight(22);
        $sheet->getRowDimension(3)->setRowHeight(20);
        $sheet->getRowDimension(4)->setRowHeight(20);
        $sheet->getRowDimension(6)->setRowHeight(22);
        $sheet->getRowDimension(7)->setRowHeight(22);

        $logoPath = MediaAsset::publicImageLocalPath('urs_logo.jpg');
        if ($logoPath && is_file($logoPath)) {
            $drawing = new Drawing;
            $drawing->setName('URS Logo');
            $drawing->setDescription('URS Logo');
            $drawing->setPath($logoPath);
            $drawing->setHeight(90);
            $drawing->setCoordinates('B1');
            $drawing->setOffsetX(8);
            $drawing->setOffsetY(2);
            $drawing->setWorksheet($sheet);
        }
    }

    private function renderTable(Worksheet $sheet, Collection $rows): int
    {
        $headerRow = 10;

        $sheet->setCellValue('B10', 'NAME OF EMPLOYEE');
        $sheet->setCellValue('C10', 'STRATEGIC OBJECTIVES');
        $sheet->setCellValue('D10', '35%');
        $sheet->setCellValue('E10', 'CORE FUNCTION/S');
        $sheet->setCellValue('F10', '55%');
        $sheet->setCellValue('G10', 'SUPPORT FUNCTION/S');
        $sheet->setCellValue('H10', '10%');
        $sheet->setCellValue('I10', 'TOTAL');
        $sheet->setCellValue('J10', 'EQUIVALENT ADJECTIVAL RATING');

        $sheet->getRowDimension($headerRow)->setRowHeight(60);

        $sheet->getStyle('B10:J10')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 13,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF8F8F8'],
            ],
            'borders' => [
                'allBorders' => self::THIN_BORDER,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        $row = 11;
        $index = 1;

        foreach ($rows as $leader) {
            $sheet->mergeCells("B{$row}:J{$row}");
            $sheet->setCellValue("B{$row}", (string) ($leader['employee_label'] ?? ''));
            $sheet->getStyle("B{$row}")->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle("B{$row}:J{$row}")->applyFromArray([
                'borders' => ['allBorders' => self::THIN_BORDER],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(24);

            $row++;

            $employeeName = trim((string) ($leader['employee_name'] ?? ''));
            $displayName = $employeeName !== '' ? $employeeName : 'N/A';

            $sheet->setCellValue("B{$row}", $index.'. '.$displayName);

            $strategicPoints = $this->asNullableFloat($leader['strategic_score'] ?? null);
            $corePoints = $this->asNullableFloat($leader['core_score'] ?? null);
            $supportPoints = $this->asNullableFloat($leader['support_score'] ?? null);
            $total = $this->asNullableFloat($leader['total_score'] ?? null);

            $this->setNumericOrBlank($sheet, "C{$row}", $this->toRawScale($strategicPoints, 35));
            $this->setNumericOrBlank($sheet, "D{$row}", $strategicPoints);
            $this->setNumericOrBlank($sheet, "E{$row}", $this->toRawScale($corePoints, 55));
            $this->setNumericOrBlank($sheet, "F{$row}", $corePoints);
            $this->setNumericOrBlank($sheet, "G{$row}", $this->toRawScale($supportPoints, 10));
            $this->setNumericOrBlank($sheet, "H{$row}", $supportPoints);
            $this->setNumericOrBlank($sheet, "I{$row}", $total);
            $sheet->setCellValue("J{$row}", (string) ($leader['adjectival_rating'] ?? ''));

            $sheet->getStyle("B{$row}:J{$row}")->applyFromArray([
                'borders' => ['allBorders' => self::THIN_BORDER],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ]);

            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("C{$row}:I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}:I{$row}")->getNumberFormat()->setFormatCode('0.00');
            $sheet->getRowDimension($row)->setRowHeight(24);

            $row++;
            $index++;
        }

        if ($rows->isEmpty()) {
            $sheet->mergeCells('B11:J11');
            $sheet->setCellValue('B11', 'No dean or director records found.');
            $sheet->getStyle('B11:J11')->applyFromArray([
                'font' => ['italic' => true],
                'borders' => ['allBorders' => self::THIN_BORDER],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            $sheet->getRowDimension(11)->setRowHeight(24);
            $row = 12;
        }

        return $row - 1;
    }

    private function renderScaleAndSignatures(Worksheet $sheet, int $startRow, array $preparedBy, array $notedBy): void
    {
        $scaleStart = max($startRow, 20);

        $sheet->setCellValue("D{$scaleStart}", 'SCALE:');
        $sheet->getStyle("D{$scaleStart}")->getFont()->setSize(12);

        $scaleRows = [
            ['Outstanding', '4.50 - 5.00'],
            ['Very Satisfactory', '3.50 - 4.49'],
            ['Satisfactory', '2.50 - 3.49'],
            ['Unsatisfactory', '1.50 - 2.49'],
            ['Poor', '0.00 -1.49'],
        ];

        foreach ($scaleRows as $offset => $scaleRow) {
            $row = $scaleStart + $offset;
            $sheet->setCellValue("E{$row}", $scaleRow[0]);
            $sheet->setCellValue("F{$row}", $scaleRow[1]);
            $sheet->getStyle("E{$row}:F{$row}")->getFont()->setItalic(true)->setSize(12);
            $sheet->getStyle("E{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        $signatureLabelRow = $scaleStart + 6;
        $sheet->setCellValue("B{$signatureLabelRow}", 'Prepared by:');
        $sheet->setCellValue("H{$signatureLabelRow}", 'Noted by:');
        $sheet->getStyle("B{$signatureLabelRow}:H{$signatureLabelRow}")->getFont()->setSize(13);

        $nameRow = $signatureLabelRow + 2;
        $preparedName = strtoupper(trim((string) ($preparedBy['name'] ?? '')));
        $preparedPosition = trim((string) ($preparedBy['position'] ?? ''));

        $notedName = strtoupper(trim((string) ($notedBy['name'] ?? '')));
        $notedPosition = trim((string) ($notedBy['position'] ?? ''));

        $sheet->mergeCells("B{$nameRow}:D{$nameRow}");
        $sheet->mergeCells('B'.($nameRow + 1).':D'.($nameRow + 1));
        $sheet->mergeCells("H{$nameRow}:J{$nameRow}");
        $sheet->mergeCells('H'.($nameRow + 1).':J'.($nameRow + 1));

        $sheet->setCellValue("B{$nameRow}", $preparedName);
        $sheet->setCellValue('B'.($nameRow + 1), $preparedPosition);

        $sheet->setCellValue("H{$nameRow}", $notedName);
        $sheet->setCellValue('H'.($nameRow + 1), $notedPosition);

        $sheet->getStyle("B{$nameRow}:D{$nameRow}")->getFont()->setBold(true)->setUnderline(true)->setSize(14);
        $sheet->getStyle("H{$nameRow}:J{$nameRow}")->getFont()->setBold(true)->setUnderline(true)->setSize(14);
        $sheet->getStyle('B'.($nameRow + 1).':D'.($nameRow + 1))->getFont()->setSize(13);
        $sheet->getStyle('H'.($nameRow + 1).':J'.($nameRow + 1))->getFont()->setSize(13);
    }

    private function setNumericOrBlank(Worksheet $sheet, string $cell, ?float $value): void
    {
        if ($value === null) {
            $sheet->setCellValue($cell, '');

            return;
        }

        $sheet->setCellValue($cell, round($value, 2));
    }

    private function toRawScale(?float $weightedPoints, float $weight): ?float
    {
        if ($weightedPoints === null || $weight <= 0) {
            return null;
        }

        return round(($weightedPoints / $weight) * 5, 2);
    }

    private function asNullableFloat($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }
}
