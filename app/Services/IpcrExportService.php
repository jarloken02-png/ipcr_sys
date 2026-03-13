<?php

namespace App\Services;

use App\Models\IpcrSubmission;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class IpcrExportService
{
    /**
     * Column letters for the 8 IPCR columns.
     */
    private const COLUMNS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

    /**
     * All main section headers use bright yellow, matching the template.
     */
    private const SECTION_HEADER_COLOR = 'FFFFFF00';

    /**
     * SO sub-header background colour (light blue, matching template).
     */
    private const SO_HEADER_COLOR = 'FFDCE6F1';

    /**
     * Standard thin border style reusable array.
     */
    private const THIN_BORDER = [
        'borderStyle' => Border::BORDER_THIN,
        'color' => ['argb' => 'FF000000'],
    ];

    /**
     * Export an IPCR document (submission, saved copy, or template) to .xlsx.
     *
     * @param  Model  $document  Any Eloquent model with table_body_html, title, etc.
     * @return string  Absolute path to the generated file.
     */
    public function export(Model $document): string
    {
        $templatePath = public_path('template/IPCR Sample.xlsx');
        $spreadsheet  = IOFactory::load($templatePath);
        $sheet        = $spreadsheet->getActiveSheet();

        // ── 1.  Update header placeholders ──────────────────────────
        $user = $document->user;
        $this->fillHeaderData($sheet, $user, $document);

        // ── 2.  Parse the stored HTML table body ────────────────────
        $parsedRows = $this->parseTableBodyHtml($document->table_body_html);

        // ── 3.  Clear existing data area (row 16 → last row) ───────
        $this->clearDataArea($sheet);

        // ── 4.  Write parsed data rows starting at row 16 ──────────
        $currentRow = 16;
        $sectionRatings = [];
        $currentSection = null;

        foreach ($parsedRows as $row) {
            switch ($row['type']) {
                case 'section-header':
                    $currentSection = $row['section'] ?? 'default';
                    $currentRow = $this->writeSectionHeader($sheet, $currentRow, $row['text']);
                    break;

                case 'so-header':
                    $currentRow = $this->writeSOHeader($sheet, $currentRow, $row['text']);
                    break;

                case 'data':
                    $currentRow = $this->writeDataRow($sheet, $currentRow, $row['cells']);
                    if ($currentSection && !empty($row['cells'][6])) {
                        $sectionRatings[$currentSection][] = (float) $row['cells'][6];
                    }
                    break;
            }
        }

        // ── 5.  Empty separator row ────────────────────────────────
        $currentRow++;

        // ── 6.  Write summary / footer section ─────────────────────
        $currentRow = $this->writeSummarySection($sheet, $currentRow, $sectionRatings, $document->noted_by, $document->approved_by);

        // ── 7.  Save to temp file ──────────────────────────────────
        $outputDir = storage_path('app/exports');
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $safeName   = preg_replace('/[^a-zA-Z0-9_-]/', '_', $document->title);
        $outputPath = $outputDir . '/IPCR_' . $safeName . '_' . $document->id . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        $writer->save($outputPath);

        return $outputPath;
    }

    /* ================================================================
     *  CLEAR DATA AREA
     * ================================================================ */

    private function clearDataArea($sheet): void
    {
        $lastRow = $sheet->getHighestRow();

        // First un-merge all cells from row 16 downward
        foreach ($sheet->getMergeCells() as $mergeRange) {
            if (preg_match('/(\d+)/', $mergeRange, $m) && (int) $m[1] >= 16) {
                $sheet->unmergeCells($mergeRange);
            }
        }

        // Then clear cell values and reset styles/row heights
        for ($r = 16; $r <= $lastRow; $r++) {
            for ($c = 0; $c < 8; $c++) {
                $cell = self::COLUMNS[$c] . $r;
                $sheet->setCellValue($cell, '');
                $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_NONE);
                $sheet->getStyle($cell)->getFont()->setBold(false)->setSize(10);
                $sheet->getStyle($cell)->getBorders()->getTop()->setBorderStyle(Border::BORDER_NONE);
                $sheet->getStyle($cell)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_NONE);
                $sheet->getStyle($cell)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_NONE);
                $sheet->getStyle($cell)->getBorders()->getRight()->setBorderStyle(Border::BORDER_NONE);
            }
            $sheet->getRowDimension($r)->setRowHeight(-1);
        }
    }

    /* ================================================================
     *  HEADER DATA
     * ================================================================ */

    private function fillHeaderData($sheet, $user, Model $document): void
    {
        $name        = $user->name ?? '';
        $designation = $this->resolveUserDesignation($user);
        $period      = $document->semester . ' ' . $document->school_year;

        // ── Row 4 – commitment paragraph (RichText) ─────────────────
        $a4 = $sheet->getCell('A4')->getValue();
        $a4Text = $a4 instanceof RichText
            ? $this->flattenRichText($a4)
            : (string) $a4;
        $a4Text = $this->normalizeCommitmentText($a4Text, $name, $designation, $period);
        $sheet->setCellValue('A4', $this->buildCommitmentRichText($a4Text, $name, $designation));

        // ── Row 5 – ratee name (bold + underline) ───────────────────
        // Handle both placeholder string and sample data
        $g5 = $sheet->getCell('G5')->getValue();
        $sheet->setCellValue('G5', $name);
        $sheet->getStyle('G5')->getFont()->setBold(true)->setUnderline(true);
        $sheet->getStyle('G5')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ── Rows 10-11 – Noted by / Approved by (top header) ────────
        $notedBy   = $document->noted_by ?? '';
        $approvedBy = $document->approved_by ?? '';

        if ($notedBy) {
            $sheet->setCellValue('A10', $notedBy);
            $sheet->getStyle('A10')->getFont()->setBold(true)->setUnderline(true);
            $sheet->getStyle('A10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $notedPos = $this->lookupUserPosition($notedBy);
            if ($notedPos) {
                $sheet->setCellValue('A11', $notedPos);
                $sheet->getStyle('A11')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }
        if ($approvedBy) {
            $sheet->setCellValue('D10', $approvedBy);
            $sheet->getStyle('D10')->getFont()->setBold(true)->setUnderline(true);
            $sheet->getStyle('D10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $approvedPos = $this->lookupUserPosition($approvedBy);
            if ($approvedPos) {
                $sheet->setCellValue('D11', $approvedPos);
                $sheet->getStyle('D11')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }
    }

    /* ================================================================
     *  HTML PARSING
     * ================================================================ */

    /**
     * Parse the table_body_html into a structured array.
     */
    private function parseTableBodyHtml(string $html): array
    {
        $rows = [];

        if (empty(trim($html))) {
            return $rows;
        }

        $wrappedHtml = '<table><tbody>' . $html . '</tbody></table>';

        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $wrappedHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $xpath = new DOMXPath($dom);
        $trNodes = $xpath->query('//tr');

        foreach ($trNodes as $tr) {
            $class = $tr->getAttribute('class') ?? '';

            if ($this->isSectionHeader($class)) {
                $rows[] = [
                    'type'    => 'section-header',
                    'text'    => $this->extractSectionHeaderText($tr, $xpath),
                    'section' => $this->detectSectionType($class),
                ];
            } elseif ($this->isSOHeader($class)) {
                $rows[] = [
                    'type' => 'so-header',
                    'text' => $this->extractSOHeaderText($tr, $xpath),
                ];
            } else {
                $rows[] = [
                    'type'  => 'data',
                    'cells' => $this->extractDataCells($tr, $xpath),
                ];
            }
        }

        return $rows;
    }

    private function isSectionHeader(string $class): bool
    {
        return str_contains($class, 'bg-green-100')
            || str_contains($class, 'bg-purple-100')
            || str_contains($class, 'bg-orange-100')
            || (str_contains($class, 'bg-gray-100') && !str_contains($class, 'bg-blue-100'));
    }

    private function isSOHeader(string $class): bool
    {
        return str_contains($class, 'bg-blue-100');
    }

    private function detectSectionType(string $class): string
    {
        if (str_contains($class, 'bg-green-100')) return 'strategic-objectives';
        if (str_contains($class, 'bg-purple-100')) return 'core-functions';
        if (str_contains($class, 'bg-orange-100')) return 'support-function';
        return 'default';
    }

    private function extractSectionHeaderText($tr, DOMXPath $xpath): string
    {
        $inputs = $xpath->query('.//input[@type="hidden"]', $tr);
        if ($inputs->length > 0) {
            $val = $inputs->item(0)->getAttribute('value');
            if (!empty(trim($val))) return trim($val);
        }

        $divs = $xpath->query('.//div', $tr);
        if ($divs->length > 0) {
            $val = trim($divs->item(0)->textContent);
            if (!empty($val)) return $val;
        }

        $textInputs = $xpath->query('.//input[@type="text"]', $tr);
        if ($textInputs->length > 0) {
            $val = $textInputs->item(0)->getAttribute('value');
            if (!empty(trim($val))) return trim($val);
        }

        $tds = $xpath->query('.//td', $tr);
        if ($tds->length > 0) {
            return trim($tds->item(0)->textContent);
        }

        return '';
    }

    /**
     * Extract SO sub-header text (e.g. "SO I. PROMOTING ACCESS TO QUALITY EDUCATION").
     */
    private function extractSOHeaderText($tr, DOMXPath $xpath): string
    {
        $parts = [];

        $spans = $xpath->query('.//span', $tr);
        if ($spans->length > 0) {
            $label = trim($spans->item(0)->textContent);
            // Normalize "SO I:" → "SO I." to match the template style
            $label = preg_replace('/^(SO\s+[IVXLCDM]+):/', '$1.', $label);
            $parts[] = $label;
        }

        $inputs = $xpath->query('.//input[@type="text"]', $tr);
        if ($inputs->length > 0) {
            $val = trim($inputs->item(0)->getAttribute('value'));
            if (!empty($val)) {
                $parts[] = strtoupper($val);
            }
        }

        if (!empty($parts)) {
            return implode(' ', $parts);
        }

        return trim($tr->textContent);
    }

    /**
     * Extract data cells from a regular data row.
     */
    private function extractDataCells($tr, DOMXPath $xpath): array
    {
        $cells = [];
        $tds = $xpath->query('.//td', $tr);

        for ($i = 0; $i < 8; $i++) {
            if ($i >= $tds->length) {
                $cells[] = '';
                continue;
            }

            $td = $tds->item($i);

            $textareas = $xpath->query('.//textarea', $td);
            if ($textareas->length > 0) {
                $cells[] = trim($textareas->item(0)->textContent);
                continue;
            }

            $inputs = $xpath->query('.//input', $td);
            if ($inputs->length > 0) {
                $cells[] = trim($inputs->item(0)->getAttribute('value'));
                continue;
            }

            $cells[] = trim($td->textContent);
        }

        return $cells;
    }

    /* ================================================================
     *  WRITING ROWS TO SPREADSHEET
     * ================================================================ */

    /**
     * Write a section header row (yellow bg, bold, left-aligned, merged A:H).
     */
    private function writeSectionHeader($sheet, int $row, string $text): int
    {
        $sheet->mergeCells("A{$row}:H{$row}");
        $sheet->setCellValue("A{$row}", strtoupper($text));

        $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
                'name' => 'Arial',
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => self::SECTION_HEADER_COLOR],
            ],
            'borders' => [
                'allBorders' => self::THIN_BORDER,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        return $row + 1;
    }

    /**
     * Write an SO sub-header row (light blue bg, bold, LEFT-aligned, merged A:H).
     */
    private function writeSOHeader($sheet, int $row, string $text): int
    {
        $sheet->mergeCells("A{$row}:H{$row}");
        $sheet->setCellValue("A{$row}", $text);

        $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
                'name' => 'Arial',
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => self::SO_HEADER_COLOR],
            ],
            'borders' => [
                'allBorders' => self::THIN_BORDER,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        return $row + 1;
    }

    /**
     * Write a data row. Default row height (no forced sizing).
     */
    private function writeDataRow($sheet, int $row, array $cells): int
    {
        for ($i = 0; $i < 8; $i++) {
            $col   = self::COLUMNS[$i];
            $value = $cells[$i] ?? '';

            if ($i >= 3 && $i <= 6 && is_numeric($value) && $value !== '') {
                $sheet->setCellValue("{$col}{$row}", (float) $value);
            } else {
                $sheet->setCellValue("{$col}{$row}", $value);
            }
        }

        // All cells: thin borders, wrap text, vertical center
        $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
            'font' => [
                'size' => 10,
                'name' => 'Arial',
            ],
            'borders' => [
                'allBorders' => self::THIN_BORDER,
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        // Text columns (A, B, C) → horizontal center
        $sheet->getStyle("A{$row}:C{$row}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Rating columns (D-G) → horizontal center
        $sheet->getStyle("D{$row}:G{$row}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Remarks (H) → horizontal center
        $sheet->getStyle("H{$row}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return $row + 1;
    }

    /* ================================================================
     *  SUMMARY / FOOTER SECTION
     * ================================================================ */

    /**
     * Write the summary section matching the template layout exactly:
     *   Row N:   Strategic Objectives: | ... | Total Overall Rating:   | ...
     *   Row N+1: Core Functions:       | ... | Final Average Rating:   | [value]
     *   Row N+2: Support Function:     | ... | Adjectival Rating:      | [value]
     *   Row N+3: Comments/Recommendations for Development Purposes:
     *   Row N+4: [comment area]
     *   Row N+5: [comment area]
     *   Row N+6: (blank)
     *   Row N+7: Calibrated by:        |     | Approved by:
     *   Row N+8: (PMT Chairperson)     |     | (Agency Head)
     */
    private function writeSummarySection($sheet, int $row, array $sectionRatings, ?string $notedBy = null, ?string $approvedBy = null): int
    {
        $sectionLabels = [
            'strategic-objectives' => 'Strategic Objectives:',
            'core-functions'       => 'Core Functions:',
            'support-function'     => 'Support Function:',
        ];

        $overallSum   = 0;
        $overallCount = 0;
        $sectionAverages = [];

        foreach ($sectionLabels as $key => $label) {
            $ratings = $sectionRatings[$key] ?? [];
            $avg = count($ratings) > 0 ? array_sum($ratings) / count($ratings) : 0;
            $sectionAverages[$key] = $avg;
            $overallSum   += array_sum($ratings);
            $overallCount += count($ratings);
        }

        $finalAverage = $overallCount > 0 ? $overallSum / $overallCount : 0;

        // ── Strategic Objectives / Total Overall Rating ─────────────
        $sheet->setCellValue("A{$row}", 'Strategic Objectives:');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(10);
        $sheet->setCellValue("D{$row}", 'Total Overall Rating:');
        $sheet->getStyle("D{$row}")->getFont()->setBold(true)->setSize(10);
        $this->applyBorderRow($sheet, $row);
        $row++;

        // ── Core Functions / Final Average Rating ───────────────────
        $sheet->setCellValue("A{$row}", 'Core Functions:');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(10);
        $sheet->setCellValue("D{$row}", 'Final Average Rating:');
        $sheet->getStyle("D{$row}")->getFont()->setBold(true)->setSize(10);
        if ($finalAverage > 0) {
            $sheet->setCellValue("G{$row}", number_format($finalAverage, 2));
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $this->applyBorderRow($sheet, $row);
        $row++;

        // ── Support Function / Adjectival Rating ────────────────────
        $sheet->setCellValue("A{$row}", 'Support Function:');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(10);
        $sheet->setCellValue("D{$row}", 'Adjectival Rating:');
        $sheet->getStyle("D{$row}")->getFont()->setBold(true)->setSize(10);
        if ($finalAverage > 0) {
            $sheet->setCellValue("G{$row}", $this->getAdjectivalRating($finalAverage));
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $this->applyBorderRow($sheet, $row);
        $row++;

        // ── Comments/Recommendations ────────────────────────────────
        $sheet->mergeCells("A{$row}:H{$row}");
        $sheet->setCellValue("A{$row}", 'Comments/Recommendations for Development Purposes:');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(10);
        $this->applyBorderRow($sheet, $row);
        $row++;

        // Two blank comment rows
        for ($i = 0; $i < 2; $i++) {
            $this->applyBorderRow($sheet, $row);
            $row++;
        }

        // Blank separator
        $row++;

        // ── Signature section ───────────────────────────────────────
        // Row N: labels
        $sheet->setCellValue("B{$row}", 'Noted by:');
        $sheet->getStyle("B{$row}")->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->setCellValue("F{$row}", 'Approved by:');
        $sheet->getStyle("F{$row}")->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $row++;

        // Row N+1: blank (signature space)
        $row++;

        // Row N+2: names (bold + underlined)
        if ($notedBy) {
            $sheet->setCellValue("B{$row}", $notedBy);
            $sheet->getStyle("B{$row}")->getFont()->setBold(true)->setUnderline(true)->setSize(10);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        if ($approvedBy) {
            $sheet->setCellValue("F{$row}", $approvedBy);
            $sheet->getStyle("F{$row}")->getFont()->setBold(true)->setUnderline(true)->setSize(10);
            $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $row++;

        // Row N+3: position titles (looked up from user records)
        $notedPosition = $this->lookupUserPosition($notedBy);
        $approvedPosition = $this->lookupUserPosition($approvedBy);

        if ($notedPosition) {
            $sheet->setCellValue("B{$row}", $notedPosition);
            $sheet->getStyle("B{$row}")->getFont()->setSize(9);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        if ($approvedPosition) {
            $sheet->setCellValue("F{$row}", $approvedPosition);
            $sheet->getStyle("F{$row}")->getFont()->setSize(9);
            $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return $row + 1;
    }

    /**
     * Look up a user's designation + department by name for the position line.
     */
    private function lookupUserPosition(?string $name): string
    {
        if (empty($name)) {
            return '';
        }

        $user = User::with(['designation', 'department'])
            ->where('name', $name)
            ->first();

        if (!$user) {
            return '';
        }

        $parts = [];
        if ($user->designation) {
            $parts[] = $user->designation->title;
        }
        if ($user->department) {
            $parts[] = $user->department->name;
        }

        return implode(', ', $parts);
    }

    /**
     * Resolve the export user's designation text.
     */
    private function resolveUserDesignation($user): string
    {
        if (!$user) {
            return '';
        }

        if ($user->relationLoaded('designation')) {
            return (string) optional($user->designation)->title;
        }

        return (string) optional($user->designation)->title;
    }

    /**
     * Convert spreadsheet RichText to plain text.
     */
    private function flattenRichText(RichText $richText): string
    {
        $text = '';
        foreach ($richText->getRichTextElements() as $element) {
            $text .= $element->getText();
        }

        return $text;
    }

    /**
     * Normalize A4 sentence so name/designation/period always match export data.
     */
    private function normalizeCommitmentText(string $text, string $name, string $designation, string $period): string
    {
        $text = preg_replace('/<Faculty_Name>|<FACULTY_NAME>/i', $name, $text);
        $text = str_replace(['<Designation in Bold>', '<Designation>', '<DESIGNATION>'], $designation, $text);
        $text = preg_replace('/January \d{4} to \w+ \d{4}/i', $period, $text);
        $text = str_replace('<Rate_Period + Year>', $period, $text);

        // Enforce the required opening format:
        // "I, <Full name>, <Designation> of"
        if (!empty($name) && !empty($designation)) {
            $opening = 'I, ' . $name . ', ' . $designation . ' of ';
            if (preg_match('/^\s*I,\s*.*?\s+of\s+/iu', $text)) {
                $text = preg_replace('/^\s*I,\s*.*?\s+of\s+/iu', $opening, $text, 1);
            } else {
                $text = $opening . ltrim($text);
            }
        }

        if (!empty($period)) {
            $text = preg_replace('/(for the period\s*)([^.]+)(\.)/iu', '$1' . $period . '$3', $text, 1);
        }

        return $text;
    }

    /**
     * Build a RichText value where name is bold+underline and designation is bold.
     */
    private function buildCommitmentRichText(string $text, string $name, string $designation): RichText
    {
        $richText = new RichText();
        $prefix = (!empty($name) && !empty($designation))
            ? 'I, ' . $name . ', ' . $designation
            : '';

        if ($prefix !== '' && str_starts_with($text, $prefix)) {
            $run = $richText->createTextRun($prefix);
            $run->getFont()->setBold(true);
            $richText->createText(substr($text, strlen($prefix)));
            return $richText;
        }

        $tokens = array_values(array_filter(array_unique([$name, $designation])));

        if (count($tokens) === 0) {
            $richText->createText($text);
            return $richText;
        }

        $offset = 0;
        $textLen = strlen($text);

        while ($offset < $textLen) {
            $nextPos = null;
            $nextToken = null;

            foreach ($tokens as $token) {
                $pos = strpos($text, $token, $offset);
                if ($pos !== false && ($nextPos === null || $pos < $nextPos)) {
                    $nextPos = $pos;
                    $nextToken = $token;
                }
            }

            if ($nextPos === null || $nextToken === null) {
                $richText->createText(substr($text, $offset));
                break;
            }

            if ($nextPos > $offset) {
                $richText->createText(substr($text, $offset, $nextPos - $offset));
            }

            $run = $richText->createTextRun($nextToken);
            $run->getFont()->setBold(true);

            $offset = $nextPos + strlen($nextToken);
        }

        return $richText;
    }

    /**
     * Apply thin borders to all 8 columns in a row.
     */
    private function applyBorderRow($sheet, int $row): void
    {
        $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
            'borders' => [
                'allBorders' => self::THIN_BORDER,
            ],
        ]);
    }

    /**
     * Convert a numeric rating to its adjectival equivalent.
     */
    private function getAdjectivalRating(float $rating): string
    {
        if ($rating >= 4.5) return 'Outstanding';
        if ($rating >= 3.5) return 'Very Satisfactory';
        if ($rating >= 2.5) return 'Satisfactory';
        if ($rating >= 1.5) return 'Unsatisfactory';
        if ($rating > 0)    return 'Poor';
        return '';
    }
}
