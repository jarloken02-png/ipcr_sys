<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;

class SoLabelNormalizer
{
    /**
     * Renumber SO headers continuously (SO I, SO II, ...) across the full table body.
     */
    public function normalizeTableBodyHtml(string $tableBodyHtml): string
    {
        if (trim($tableBodyHtml) === '') {
            return $tableBodyHtml;
        }

        [$dom, $xpath] = $this->loadTableBodyHtml($tableBodyHtml);
        if (! $dom || ! $xpath) {
            return $tableBodyHtml;
        }

        $soRows = $xpath->query('//tr[contains(concat(" ", normalize-space(@class), " "), " bg-blue-100 ")]');
        $soNumber = 1;

        foreach ($soRows as $row) {
            $span = $xpath->query(
                './/span[contains(concat(" ", normalize-space(@class), " "), " font-semibold ") and contains(concat(" ", normalize-space(@class), " "), " text-gray-800 ")]',
                $row
            )->item(0);

            if (! $span) {
                continue;
            }

            $span->nodeValue = 'SO ' . $this->toRoman($soNumber) . ':';
            $soNumber++;
        }

        return $this->extractTbodyHtml($dom, $xpath, $tableBodyHtml);
    }

    /**
     * Count SO rows per section from table body HTML.
     */
    public function extractSectionCounts(string $tableBodyHtml): array
    {
        $counts = [
            'strategic_objectives' => 0,
            'core_functions' => 0,
            'support_functions' => 0,
        ];

        if (trim($tableBodyHtml) === '') {
            return $counts;
        }

        [$dom, $xpath] = $this->loadTableBodyHtml($tableBodyHtml);
        if (! $dom || ! $xpath) {
            return $counts;
        }

        $rows = $xpath->query('//tr');
        $currentSection = null;

        foreach ($rows as $row) {
            $class = ' ' . preg_replace('/\s+/', ' ', trim((string) $row->getAttribute('class'))) . ' ';

            if (str_contains($class, ' bg-green-100 ')) {
                $currentSection = 'strategic_objectives';
                continue;
            }

            if (str_contains($class, ' bg-purple-100 ')) {
                $currentSection = 'core_functions';
                continue;
            }

            if (str_contains($class, ' bg-orange-100 ')) {
                $currentSection = 'support_functions';
                continue;
            }

            if (str_contains($class, ' bg-gray-100 ')) {
                $hasColspan = $xpath->query('.//td[@colspan]', $row)->length > 0;
                if ($hasColspan) {
                    $currentSection = null;
                }
                continue;
            }

            if (str_contains($class, ' bg-blue-100 ') && $currentSection !== null) {
                $counts[$currentSection]++;
            }
        }

        return $counts;
    }

    /**
     * Normalize SO labels and derive section counts in one pass from HTML.
     */
    public function normalizeAndExtractCounts(string $tableBodyHtml): array
    {
        $normalizedHtml = $this->normalizeTableBodyHtml($tableBodyHtml);

        return [
            'table_body_html' => $normalizedHtml,
            'so_count_json' => $this->extractSectionCounts($normalizedHtml),
        ];
    }

    private function loadTableBodyHtml(string $tableBodyHtml): array
    {
        $wrappedHtml = '<table><tbody>' . $tableBodyHtml . '</tbody></table>';

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $wrappedHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        if (! $loaded) {
            return [null, null];
        }

        return [$dom, new DOMXPath($dom)];
    }

    private function extractTbodyHtml(DOMDocument $dom, DOMXPath $xpath, string $fallback): string
    {
        $tbody = $xpath->query('//tbody')->item(0);
        if (! $tbody) {
            return $fallback;
        }

        $html = '';
        foreach ($tbody->childNodes as $child) {
            $html .= $dom->saveHTML($child);
        }

        return $html !== '' ? $html : $fallback;
    }

    private function toRoman(int $number): string
    {
        $map = [
            1000 => 'M',
            900 => 'CM',
            500 => 'D',
            400 => 'CD',
            100 => 'C',
            90 => 'XC',
            50 => 'L',
            40 => 'XL',
            10 => 'X',
            9 => 'IX',
            5 => 'V',
            4 => 'IV',
            1 => 'I',
        ];

        $roman = '';
        foreach ($map as $value => $numeral) {
            while ($number >= $value) {
                $roman .= $numeral;
                $number -= $value;
            }
        }

        return $roman;
    }
}
