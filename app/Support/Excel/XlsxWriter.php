<?php

namespace App\Support\Excel;

use RuntimeException;
use ZipArchive;

/**
 * Penulis XLSX minimal (multi-sheet, header tebal, filter, freeze header, lebar kolom otomatis)
 * tanpa dependensi eksternal; memerlukan ekstensi PHP zip.
 */
class XlsxWriter
{
    /** @var array<int, array{name: string, headers: array<int, string>, rows: array<int, array<int, mixed>>}> */
    private array $sheets = [];

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, mixed>>  $rows
     */
    public function addSheet(string $name, array $headers, array $rows): static
    {
        $name = preg_replace('/[\[\]\:\*\?\/\\\\]/', ' ', $name);
        $this->sheets[] = ['name' => mb_substr(trim($name) ?: 'Sheet'.(count($this->sheets) + 1), 0, 31), 'headers' => $headers, 'rows' => $rows];

        return $this;
    }

    public function toString(): string
    {
        if ($this->sheets === []) {
            $this->addSheet('Sheet1', [], []);
        }
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new ZipArchive;
        if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Tidak dapat membuat berkas XLSX.');
        }

        $types = '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        $wbSheets = '';
        $wbRels = '<Relationship Id="rIdStyles" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
        foreach ($this->sheets as $i => $sheet) {
            $n = $i + 1;
            $types .= '<Override PartName="/xl/worksheets/sheet'.$n.'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
            $wbSheets .= '<sheet name="'.$this->esc($sheet['name']).'" sheetId="'.$n.'" r:id="rId'.$n.'"/>';
            $wbRels .= '<Relationship Id="rId'.$n.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.$n.'.xml"/>';
            $zip->addFromString('xl/worksheets/sheet'.$n.'.xml', $this->sheetXml($sheet['headers'], $sheet['rows']));
        }

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/>'.$types.'</Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>'.$wbSheets.'</sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.$wbRels.'</Relationships>');
        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            .'<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFD9E1F2"/></patternFill></fill></fills>'
            .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="3"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/><xf numFmtId="4" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>');
        $zip->close();

        $content = file_get_contents($tmp);
        @unlink($tmp);

        return $content;
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, mixed>>  $rows
     */
    private function sheetXml(array $headers, array $rows): string
    {
        $cols = max(count($headers), $rows ? max(array_map('count', $rows)) : 0);
        $widths = array_fill(0, $cols, 8);
        foreach (array_merge([$headers], $rows) as $row) {
            foreach (array_values($row) as $c => $v) {
                $widths[$c] = max($widths[$c], min(60, mb_strlen((string) $v) + 2));
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        if ($headers) {
            $xml .= '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>';
        }
        if ($cols) {
            $xml .= '<cols>';
            foreach ($widths as $c => $w) {
                $xml .= '<col min="'.($c + 1).'" max="'.($c + 1).'" width="'.$w.'" customWidth="1"/>';
            }
            $xml .= '</cols>';
        }
        $xml .= '<sheetData>';
        $r = 0;
        if ($headers) {
            $r++;
            $xml .= '<row r="1">';
            foreach (array_values($headers) as $c => $h) {
                $xml .= $this->cell($c, 1, $h, 1);
            }
            $xml .= '</row>';
        }
        foreach ($rows as $row) {
            $r++;
            $xml .= '<row r="'.$r.'">';
            foreach (array_values($row) as $c => $v) {
                $xml .= $this->cell($c, $r, $v, is_float($v) ? 2 : 0);
            }
            $xml .= '</row>';
        }
        $xml .= '</sheetData>';
        if ($headers && $r > 1) {
            $xml .= '<autoFilter ref="A1:'.$this->colName(count($headers) - 1).$r.'"/>';
        }

        return $xml.'</worksheet>';
    }

    private function cell(int $c, int $r, mixed $value, int $style): string
    {
        $ref = $this->colName($c).$r;
        if ($value === null || $value === '') {
            return '<c r="'.$ref.'"/>';
        }
        if (is_int($value) || is_float($value)) {
            return '<c r="'.$ref.'" s="'.$style.'"><v>'.$value.'</v></c>';
        }

        return '<c r="'.$ref.'" s="'.$style.'" t="inlineStr"><is><t xml:space="preserve">'.$this->esc((string) $value).'</t></is></c>';
    }

    private function colName(int $index): string
    {
        $name = '';
        for ($i = $index; $i >= 0; $i = intdiv($i, 26) - 1) {
            $name = chr(65 + $i % 26).$name;
        }

        return $name;
    }

    private function esc(string $value): string
    {
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $value);
        // Cegah formula injection saat dibuka di Excel
        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@'], true) && ! is_numeric($value)) {
            $value = "'".$value;
        }

        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
