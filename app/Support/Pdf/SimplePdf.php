<?php

namespace App\Support\Pdf;

/**
 * Penghasil PDF 1.4 sederhana (teks + tabel) tanpa dependensi eksternal.
 * Memakai font standar Helvetica/Helvetica-Bold sehingga tidak perlu embed font.
 * Cocok untuk dokumen surat/invoice/receipt berformat tetap.
 */
class SimplePdf
{
    private const PAGE_W = 595.28;

    private const PAGE_H = 841.89;

    private const MARGIN = 56.0;

    /** Lebar glyph Helvetica (per 1000 em) untuk ASCII 32..126. */
    private const WIDTHS = [
        278, 278, 355, 556, 556, 889, 667, 191, 333, 333, 389, 584, 278, 333, 278, 278,
        556, 556, 556, 556, 556, 556, 556, 556, 556, 556, 278, 278, 584, 584, 584, 556, 1015,
        667, 667, 722, 722, 667, 611, 778, 722, 278, 500, 667, 556, 833, 722, 778, 667, 778, 722, 667, 611, 722, 667, 944, 667, 667, 611,
        278, 278, 278, 469, 556, 333,
        556, 556, 500, 556, 556, 278, 556, 556, 222, 222, 500, 222, 833, 556, 556, 556, 556, 333, 500, 278, 556, 500, 722, 500, 500, 500,
        334, 260, 334, 584,
    ];

    /** @var array<int, string> isi content stream per halaman */
    private array $pages = [];

    private string $buffer = '';

    private float $y;

    public function __construct()
    {
        $this->y = self::PAGE_H - self::MARGIN;
    }

    public function space(float $points = 10): static
    {
        $this->y -= $points;

        return $this;
    }

    public function line(): static
    {
        $this->ensure(8);
        $this->buffer .= sprintf("0.5 w %.2f %.2f m %.2f %.2f l S\n", self::MARGIN, $this->y, self::PAGE_W - self::MARGIN, $this->y);
        $this->y -= 8;

        return $this;
    }

    /**
     * @param  string  $align  left|center|right
     */
    public function text(string $text, float $size = 11, bool $bold = false, string $align = 'left', float $indent = 0): static
    {
        $maxWidth = self::PAGE_W - 2 * self::MARGIN - $indent;
        foreach (explode("\n", $text) as $paragraph) {
            foreach ($this->wrap($paragraph, $size, $bold, $maxWidth) as $line) {
                $lh = $size * 1.35;
                $this->ensure($lh);
                $width = $this->measure($line, $size, $bold);
                $x = self::MARGIN + $indent;
                if ($align === 'center') {
                    $x = (self::PAGE_W - $width) / 2;
                } elseif ($align === 'right') {
                    $x = self::PAGE_W - self::MARGIN - $width;
                }
                $this->y -= $size;
                $this->buffer .= sprintf("BT /%s %.2f Tf %.2f %.2f Td (%s) Tj ET\n", $bold ? 'F2' : 'F1', $size, $x, $this->y, $this->escape($line));
                $this->y -= ($lh - $size);
            }
        }

        return $this;
    }

    /**
     * Tabel sederhana. $widths dalam poin dan dijumlahkan ≤ lebar area cetak.
     *
     * @param  array<int, string>  $header
     * @param  array<int, array<int, string>>  $rows
     * @param  array<int, float>  $widths
     * @param  array<int, string>  $aligns  left|right per kolom
     */
    public function table(array $header, array $rows, array $widths, array $aligns = []): static
    {
        $size = 10.0;
        $pad = 4.0;
        $draw = function (array $cells, bool $bold) use ($size, $pad, $widths, $aligns) {
            $wrapped = [];
            $lines = 1;
            foreach ($cells as $i => $cell) {
                $wrapped[$i] = $this->wrap((string) $cell, $size, $bold, $widths[$i] - 2 * $pad);
                $lines = max($lines, count($wrapped[$i]));
            }
            $height = $lines * $size * 1.3 + 2 * $pad;
            $this->ensure($height);
            $top = $this->y;
            $x = self::MARGIN;
            foreach ($cells as $i => $cell) {
                $this->buffer .= sprintf("0.5 w %.2f %.2f %.2f %.2f re S\n", $x, $top - $height, $widths[$i], $height);
                $ly = $top - $pad;
                foreach ($wrapped[$i] as $line) {
                    $ly -= $size;
                    $w = $this->measure($line, $size, $bold);
                    $tx = ($aligns[$i] ?? 'left') === 'right' ? $x + $widths[$i] - $pad - $w : $x + $pad;
                    $this->buffer .= sprintf("BT /%s %.2f Tf %.2f %.2f Td (%s) Tj ET\n", $bold ? 'F2' : 'F1', $size, $tx, $ly, $this->escape($line));
                    $ly -= $size * 0.3;
                }
                $x += $widths[$i];
            }
            $this->y = $top - $height;
        };

        $draw($header, true);
        foreach ($rows as $row) {
            $draw($row, false);
        }

        return $this;
    }

    public function output(): string
    {
        $this->flushPage();
        if ($this->pages === []) {
            $this->pages[] = '';
        }

        $objects = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $kids = [];
        $n = count($this->pages);
        for ($i = 0; $i < $n; $i++) {
            $kids[] = (5 + $i * 2).' 0 R';
        }
        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $kids).'] /Count '.$n.' >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
        foreach ($this->pages as $i => $content) {
            $pageId = 5 + $i * 2;
            $objects[$pageId] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2f %.2f] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents %d 0 R >>',
                self::PAGE_W, self::PAGE_H, $pageId + 1
            );
            $objects[$pageId + 1] = '<< /Length '.strlen($content)." >>\nstream\n".$content.'endstream';
        }

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "$id 0 obj\n$body\nendobj\n";
        }
        $xref = strlen($pdf);
        $count = count($objects) + 1;
        $pdf .= "xref\n0 $count\n0000000000 65535 f \n";
        foreach ($offsets as $off) {
            $pdf .= sprintf("%010d 00000 n \n", $off);
        }
        $pdf .= "trailer\n<< /Size $count /Root 1 0 R >>\nstartxref\n$xref\n%%EOF\n";

        return $pdf;
    }

    private function ensure(float $needed): void
    {
        if ($this->y - $needed < self::MARGIN) {
            $this->flushPage();
            $this->y = self::PAGE_H - self::MARGIN;
        }
    }

    private function flushPage(): void
    {
        if ($this->buffer !== '') {
            $this->pages[] = $this->buffer;
            $this->buffer = '';
        }
    }

    private function toLatin(string $text): string
    {
        $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);

        return $converted === false ? preg_replace('/[^\x20-\x7E]/', '?', $text) : $converted;
    }

    private function escape(string $text): string
    {
        return str_replace(['\\', '(', ')', "\r"], ['\\\\', '\\(', '\\)', ''], $this->toLatin($text));
    }

    private function measure(string $text, float $size, bool $bold): float
    {
        $total = 0;
        foreach (str_split($this->toLatin($text)) as $ch) {
            $o = ord($ch);
            $total += ($o >= 32 && $o <= 126) ? self::WIDTHS[$o - 32] : 556;
        }

        return $total / 1000 * $size * ($bold ? 1.06 : 1.0);
    }

    /**
     * @return array<int, string>
     */
    private function wrap(string $text, float $size, bool $bold, float $maxWidth): array
    {
        $text = trim($text);
        if ($text === '') {
            return [''];
        }
        $lines = [];
        $current = '';
        foreach (preg_split('/\s+/', $text) as $word) {
            // pecah kata yang lebih panjang dari lebar baris
            while ($this->measure($word, $size, $bold) > $maxWidth && mb_strlen($word) > 1) {
                $cut = mb_strlen($word);
                while ($cut > 1 && $this->measure(mb_substr($word, 0, $cut), $size, $bold) > $maxWidth) {
                    $cut--;
                }
                if ($current !== '') {
                    $lines[] = $current;
                    $current = '';
                }
                $lines[] = mb_substr($word, 0, $cut);
                $word = mb_substr($word, $cut);
            }
            $candidate = $current === '' ? $word : $current.' '.$word;
            if ($this->measure($candidate, $size, $bold) <= $maxWidth) {
                $current = $candidate;
            } else {
                $lines[] = $current;
                $current = $word;
            }
        }
        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }
}
