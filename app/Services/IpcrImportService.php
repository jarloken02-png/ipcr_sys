<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;

class IpcrImportService
{
    /**
     * Known section header keywords that map to section types.
     */
    private const SECTION_MAP = [
        'strategic' => 'strategic-objectives',
        'core'      => 'core-functions',
        'support'   => 'support-function',
    ];

    private const SECTION_COLORS = [
        'strategic-objectives' => 'bg-green-100',
        'core-functions'       => 'bg-purple-100',
        'support-function'     => 'bg-orange-100',
    ];

    /**
     * Parse an uploaded IPCR/OPCR xlsx file and return structured data.
     *
     * @param  string  $filePath  Absolute path to the uploaded xlsx file.
     * @return array{table_body_html: string, noted_by: string, approved_by: string, title: string, school_year: string, semester: string}
     */
    public function parse(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        $lastRow = $sheet->getHighestRow();

        // ── Extract header metadata ─────────────────────────────────
        $notedBy    = $this->cellText($sheet, 'A10');
        $approvedBy = $this->cellText($sheet, 'D10');
        $title      = $this->extractTitle($sheet);
        [$schoolYear, $semester] = $this->extractPeriod($sheet);

        // ── Detect the data start row (first row after the column headers) ──
        $dataStartRow = $this->findDataStartRow($sheet, $lastRow);

        // ── Detect where data ends (summary / footer section) ───────
        $dataEndRow = $this->findDataEndRow($sheet, $dataStartRow, $lastRow);

        // ── Parse rows into structured data ─────────────────────────
        $tableBodyHtml = $this->buildTableBodyHtml($sheet, $dataStartRow, $dataEndRow);

        return [
            'table_body_html' => $tableBodyHtml,
            'noted_by'        => $notedBy,
            'approved_by'     => $approvedBy,
            'title'           => $title,
            'school_year'     => $schoolYear,
            'semester'        => $semester,
        ];
    }

    /**
     * Get plain text from a cell, handling RichText.
     */
    private function cellText($sheet, string $ref): string
    {
        $val = $sheet->getCell($ref)->getValue();
        if ($val instanceof RichText) {
            return trim($val->getPlainText());
        }
        return trim((string) $val);
    }

    /**
     * Extract the document title from the header area.
     */
    private function extractTitle($sheet): string
    {
        // Row 2 usually has "Individual Performance Commitment and Review (IPCR)"
        $row2 = $this->cellText($sheet, 'A2');
        if (!empty($row2)) {
            // Clean up line breaks
            return preg_replace('/\s+/', ' ', $row2);
        }
        return 'Imported IPCR';
    }

    /**
     * Extract school year and semester from the commitment paragraph (row 4).
     */
    private function extractPeriod($sheet): array
    {
        $text = $this->cellText($sheet, 'A4');

        // Look for "January YYYY to June YYYY" or "July YYYY to December YYYY"
        $schoolYear = '';
        $semester   = '';

        if (preg_match('/January\s+(\d{4})\s+to\s+June\s+(\d{4})/i', $text, $m)) {
            $semester   = 'January - June';
            $schoolYear = $m[1] === $m[2] ? $m[1] : $m[1] . '-' . $m[2];
        } elseif (preg_match('/July\s+(\d{4})\s+to\s+December\s+(\d{4})/i', $text, $m)) {
            $semester   = 'July - December';
            $schoolYear = $m[1] === $m[2] ? $m[1] : $m[1] . '-' . $m[2];
        } elseif (preg_match('/(\d{4})\s*[-–]\s*(\d{4})/', $text, $m)) {
            $schoolYear = $m[1] . '-' . $m[2];
        } elseif (preg_match('/(\d{4})/', $text, $m)) {
            $schoolYear = $m[1];
        }

        return [$schoolYear, $semester];
    }

    /**
     * Find the first data row (after column headers like MFO / Success Indicators).
     */
    private function findDataStartRow($sheet, int $lastRow): int
    {
        for ($r = 1; $r <= min($lastRow, 20); $r++) {
            $a = strtolower($this->cellText($sheet, "A{$r}"));
            if (str_contains($a, 'mfo')) {
                // Data starts 2 rows after (header row + sub-header row)
                return $r + 2;
            }
        }
        // Fallback: row 16 matches the IPCR Sample template
        return 16;
    }

    /**
     * Find where data rows end (summary section starts).
     * Look for "Strategic Objectives:" or "Core Functions:" as summary labels,
     * or "Calibrated by:" / "Noted by:" in the footer.
     */
    private function findDataEndRow($sheet, int $startRow, int $lastRow): int
    {
        for ($r = $startRow; $r <= $lastRow; $r++) {
            $a = $this->cellText($sheet, "A{$r}");
            $aLower = strtolower($a);

            // Summary section markers
            if (preg_match('/^strategic\s+objectives\s*:/i', $a)) return $r - 1;
            if (preg_match('/^core\s+functions\s*:/i', $a)) return $r - 1;

            // Footer markers
            if (str_contains($aLower, 'calibrated by')) return $r - 1;

            // "Comments/Recommendations" line
            if (str_contains($aLower, 'comments/recommendations')) return $r - 1;

            // Total/Overall Rating line
            $d = strtolower($this->cellText($sheet, "D{$r}"));
            if (str_contains($d, 'total overall rating')) return $r - 1;
        }

        return $lastRow;
    }

    /**
     * Build the editor-compatible table_body_html from spreadsheet rows.
     */
    private function buildTableBodyHtml($sheet, int $startRow, int $endRow): string
    {
        $html = '';
        $soCounter = 0;

        for ($r = $startRow; $r <= $endRow; $r++) {
            $rowType = $this->classifyRow($sheet, $r);

            switch ($rowType) {
                case 'section-header':
                    $text = $this->cellText($sheet, "A{$r}");
                    $section = $this->detectSection($text);
                    $colorClass = self::SECTION_COLORS[$section] ?? 'bg-gray-100';
                    $html .= $this->buildSectionHeaderRow($text, $colorClass, $section !== 'default');
                    break;

                case 'so-header':
                    $soCounter++;
                    $text = $this->cellText($sheet, "A{$r}");
                    $html .= $this->buildSOHeaderRow($text, $soCounter);
                    break;

                case 'data':
                    $cells = $this->extractRowCells($sheet, $r);
                    if ($this->isRowEmpty($cells)) continue 2;
                    $html .= $this->buildDataRow($cells);
                    break;

                case 'empty':
                    // Skip blank rows
                    break;
            }
        }

        return $html;
    }

    /**
     * Classify a spreadsheet row by its background color and merge state.
     */
    private function classifyRow($sheet, int $row): string
    {
        $bg = $this->getCellBgColor($sheet, "A{$row}");
        $aVal = $this->cellText($sheet, "A{$row}");

        // Check if merged across all columns (colspan indicator)
        $isMerged = $this->isMergedAcross($sheet, $row);

        // Yellow background = section header
        if ($bg && $this->isYellowish($bg)) {
            return 'section-header';
        }

        // Blue background = SO header
        if ($bg && $this->isBluish($bg)) {
            return 'so-header';
        }

        // If merged across and text matches section/SO patterns
        if ($isMerged && !empty($aVal)) {
            $upper = strtoupper($aVal);
            if ($this->matchesSectionKeyword($upper)) {
                return 'section-header';
            }
            if (preg_match('/^SO\s+[IVXLCDM]+/i', $upper)) {
                return 'so-header';
            }
        }

        // Check if row has any content
        $hasContent = false;
        foreach (range('A', 'H') as $col) {
            if (!empty($this->cellText($sheet, "{$col}{$row}"))) {
                $hasContent = true;
                break;
            }
        }

        return $hasContent ? 'data' : 'empty';
    }

    private function getCellBgColor($sheet, string $ref): ?string
    {
        $fill = $sheet->getStyle($ref)->getFill();
        if ($fill->getFillType() === 'none') return null;
        $color = $fill->getStartColor()->getRGB();
        return ($color && $color !== '000000') ? $color : null;
    }

    private function isYellowish(string $rgb): bool
    {
        $r = hexdec(substr($rgb, 0, 2));
        $g = hexdec(substr($rgb, 2, 2));
        $b = hexdec(substr($rgb, 4, 2));
        return $r > 200 && $g > 200 && $b < 100;
    }

    private function isBluish(string $rgb): bool
    {
        $r = hexdec(substr($rgb, 0, 2));
        $g = hexdec(substr($rgb, 2, 2));
        $b = hexdec(substr($rgb, 4, 2));
        return $b > 180 && ($b > $r || $g > 150);
    }

    private function matchesSectionKeyword(string $text): bool
    {
        foreach (array_keys(self::SECTION_MAP) as $keyword) {
            if (str_contains(strtolower($text), $keyword)) return true;
        }
        return false;
    }

    private function isMergedAcross($sheet, int $row): bool
    {
        foreach ($sheet->getMergeCells() as $range) {
            if (preg_match('/A(\d+):([A-H])(\d+)/', $range, $m)) {
                if ((int) $m[1] === $row && $m[2] >= 'F') {
                    return true;
                }
            }
        }
        return false;
    }

    private function detectSection(string $text): string
    {
        $lower = strtolower($text);
        foreach (self::SECTION_MAP as $keyword => $type) {
            if (str_contains($lower, $keyword)) return $type;
        }
        return 'default';
    }

    private function extractRowCells($sheet, int $row): array
    {
        $cells = [];
        foreach (range('A', 'H') as $col) {
            $cells[] = $this->cellText($sheet, "{$col}{$row}");
        }
        return $cells;
    }

    private function isRowEmpty(array $cells): bool
    {
        foreach ($cells as $c) {
            if (!empty(trim($c))) return false;
        }
        return true;
    }

    /* ================================================================
     *  HTML BUILDERS — produce editor-compatible table rows
     * ================================================================ */

    private function buildSectionHeaderRow(string $text, string $colorClass, bool $predefined): string
    {
        $escaped = e($text);
        if ($predefined) {
            return '<tr class="' . $colorClass . '">'
                . '<td colspan="8" class="border border-gray-300 px-3 py-2 font-semibold text-gray-800">'
                . '<div class="font-semibold text-gray-800">' . $escaped . '</div>'
                . '<input type="hidden" value="' . $escaped . '" />'
                . '</td></tr>';
        }
        // Custom section (gray)
        return '<tr class="bg-gray-100">'
            . '<td colspan="8" class="border border-gray-300 px-3 py-2 font-semibold text-gray-800">'
            . '<input type="text" class="w-full bg-transparent border-0 focus:ring-0 font-semibold text-gray-800" placeholder="Enter custom section header..." value="' . $escaped . '" />'
            . '</td></tr>';
    }

    private function buildSOHeaderRow(string $text, int $soNumber): string
    {
        $roman = $this->toRoman($soNumber);
        // Try to parse "SO XIV. DESCRIPTION" format from the xlsx
        $description = $text;
        if (preg_match('/^SO\s+[IVXLCDM]+\.?\s*(.*)/i', $text, $m)) {
            $description = trim($m[1]);
        }
        $escaped = e($description);

        return '<tr class="bg-blue-100">'
            . '<td colspan="8" class="border border-gray-300 px-3 py-2 font-semibold text-gray-800">'
            . '<div class="flex items-center gap-2">'
            . '<span class="font-semibold text-gray-800">SO ' . $roman . ':</span>'
            . '<input type="text" class="flex-1 bg-transparent border-0 focus:ring-0 font-semibold text-gray-800" placeholder="Enter SO description..." value="' . $escaped . '" />'
            . '</div></td></tr>';
    }

    private function buildDataRow(array $cells): string
    {
        // Columns: 0=MFO, 1=Success Indicators, 2=Actual Accomplishments,
        //          3=Q, 4=E, 5=T, 6=A, 7=Remarks
        $mfo     = e($cells[0] ?? '');
        $si      = e($cells[1] ?? '');
        $actual  = e($cells[2] ?? '');
        $q       = $cells[3] ?? '';
        $e       = $cells[4] ?? '';
        $t       = $cells[5] ?? '';
        $a       = $cells[6] ?? '';
        $remarks = e($cells[7] ?? '');

        $hasRatings = !empty($q) || !empty($e) || !empty($t) || !empty($a) || !empty($actual) || !empty($remarks);

        return '<tr>'
            . '<td class="border border-gray-300 px-2 py-2"><textarea class="w-full h-20 px-2 py-1 text-xs resize-none border-0 focus:ring-0" placeholder="Enter MFO">' . $mfo . '</textarea></td>'
            . '<td class="border border-gray-300 px-2 py-2"><textarea class="w-full h-20 px-2 py-1 text-xs resize-none border-0 focus:ring-0" placeholder="Enter Success Indicators">' . $si . '</textarea></td>'
            . '<td class="border border-gray-300 px-2 py-2"><textarea class="w-full h-20 px-2 py-1 text-xs resize-none border-0 focus:ring-0" placeholder="Enter Actual Accomplishments">' . $actual . '</textarea></td>'
            . '<td class="border border-gray-300 px-2 py-2"><input type="number" class="w-full h-20 px-2 py-1 text-xs text-center border-0 focus:ring-0 qeta-q" min="1" max="5" step="1" placeholder="-" value="' . e($q) . '"></td>'
            . '<td class="border border-gray-300 px-2 py-2"><input type="number" class="w-full h-20 px-2 py-1 text-xs text-center border-0 focus:ring-0 qeta-e" min="1" max="5" step="1" placeholder="-" value="' . e($e) . '"></td>'
            . '<td class="border border-gray-300 px-2 py-2"><input type="number" class="w-full h-20 px-2 py-1 text-xs text-center border-0 focus:ring-0 qeta-t" min="1" max="5" step="1" placeholder="-" value="' . e($t) . '"></td>'
            . '<td class="border border-gray-300 px-2 py-2"><input type="number" class="w-full h-20 px-2 py-1 text-xs text-center border-0 focus:ring-0 qeta-a" min="1" max="5" step="0.01" placeholder="-" readonly style="background-color: #f3f4f6;" title="Auto-computed average of Q, E, T" value="' . e($a) . '"></td>'
            . '<td class="border border-gray-300 px-2 py-2"><textarea class="w-full h-20 px-2 py-1 text-xs resize-none border-0 focus:ring-0" placeholder="Enter Remarks">' . $remarks . '</textarea></td>'
            . '</tr>';
    }

    private function toRoman(int $num): string
    {
        $map = [
            1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD',
            100 => 'C', 90 => 'XC', 50 => 'L', 40 => 'XL',
            10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV', 1 => 'I',
        ];
        $result = '';
        foreach ($map as $value => $numeral) {
            while ($num >= $value) {
                $result .= $numeral;
                $num -= $value;
            }
        }
        return $result;
    }
}
