<?php

namespace Tests\Unit;

use App\Support\Excel\XlsxWriter;
use App\Support\Pdf\SimplePdf;
use PHPUnit\Framework\TestCase;
use ZipArchive;

class SupportWritersTest extends TestCase
{
    public function test_pdf_is_valid_and_paginates_long_content(): void
    {
        $pdf = new SimplePdf;
        $pdf->text('LETTER OF ACCEPTANCE (Ünïcode) \\ test', 16, true, 'center');
        for ($i = 0; $i < 120; $i++) {
            $pdf->text("Baris $i ".str_repeat('lorem ipsum ', 12));
        }
        $pdf->table(['Item', 'Jumlah'], [['Registrasi', '1.500.000,00']], [300, 139], ['left', 'right']);
        $out = $pdf->output();

        $this->assertStringStartsWith('%PDF-1.4', $out);
        $this->assertStringEndsWith("%%EOF\n", $out);
        $this->assertGreaterThan(1, substr_count($out, '/Type /Page /'));
    }

    public function test_xlsx_contains_headers_rows_and_escapes_formulas(): void
    {
        $xlsx = (new XlsxWriter)
            ->addSheet('Peserta', ['Nama', 'Total'], [['Budi & Co', 1500000.5], ['=HYPERLINK("x")', 3]])
            ->toString();

        $tmp = tempnam(sys_get_temp_dir(), 't');
        file_put_contents($tmp, $xlsx);
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($tmp));
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $this->assertStringContainsString('Budi &amp; Co', $sheet);
        $this->assertStringContainsString('<v>1500000.5</v>', $sheet);
        $this->assertStringContainsString("&apos;=HYPERLINK", $sheet);
        $this->assertStringContainsString('autoFilter', $sheet);
        $zip->close();
        unlink($tmp);
    }
}
