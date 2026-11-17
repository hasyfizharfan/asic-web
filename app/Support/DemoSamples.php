<?php

namespace App\Support;

use App\Models\AbstractReview;
use App\Models\AbstractSubmission;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\DigitalSignature;
use App\Models\DocumentTemplate;
use App\Models\Event;
use App\Models\EventPhoto;
use App\Models\EventSession;
use App\Models\Invoice;
use App\Models\IssuedDocument;
use App\Models\LegalDocument;
use App\Models\Participant;
use App\Models\PaymentProof;
use App\Models\PaymentVerification;
use App\Models\Registration;
use App\Models\RegistrationPeriod;
use App\Models\Role;
use App\Models\SponsorshipInquiry;
use App\Models\UserAccount;
use App\Services\AbstractDecisionProcessor;
use App\Services\DocumentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Throwable;

/**
 * Contoh isi untuk setiap menu portal (tanda tangan, template unduhan, sesi + QR, peserta di berbagai tahap
 * beserta dokumen resmi yang diterbitkan lewat alur asli, pengumuman, dokumen legal, galeri, sponsor, arsip).
 * Semua yang dibuat dicatat di $created agar `asic:demo-landing --remove` bisa menghapusnya bersih.
 */
class DemoSamples
{
    /** @param  array<string, list<mixed>>  $created */
    public function __construct(private array $created) {}

    /** @return array<string, list<mixed>> */
    public function created(): array
    {
        return $this->created;
    }

    private function track(string $type, mixed $value): void
    {
        $this->created[$type][] = $value;
    }

    private function tracked(string $type): bool
    {
        return ! empty($this->created[$type]);
    }

    public function seed(Event $event): array
    {
        $done = [];
        Mail::fake(); // dokumen contoh tidak dikirim ke email mana pun
        foreach (['signatures', 'templates', 'sessions', 'participants', 'announcement', 'legal', 'photos', 'inquiry', 'archive'] as $step) {
            $key = 'samples_'.$step;
            if ($this->tracked($key)) {
                continue;
            }
            $this->{$step}($event);
            $this->track($key, true);
            $done[] = $step;
        }

        return $done;
    }

    // ---------------------------------------------------------------- tanda tangan & stempel

    private function signatures(Event $event): void
    {
        foreach ([[Role::ROLE_ADMIN, 'Ketua'], [Role::ROLE_BENDAHARA, 'Bendahara']] as [$roleName, $label]) {
            $role = Role::where('nama_role', $roleName)->first();
            if (! $role || DigitalSignature::where('id_role', $role->id_role)->exists()) {
                continue;
            }
            $ttd = 'signatures/ttd/demo-'.Str::slug($label).'.png';
            $stempel = 'signatures/stempel/demo-'.Str::slug($label).'.png';
            Storage::disk('local')->put($ttd, $this->signaturePng($label === 'Ketua' ? 1 : 2));
            Storage::disk('local')->put($stempel, $this->stampPng($label === 'Ketua' ? 'KETUA PANITIA' : 'BENDAHARA'));
            $sig = DigitalSignature::create(['id_role' => $role->id_role, 'file_ttd' => $ttd, 'file_stempel' => $stempel, 'tanggal_upload' => now()]);
            $this->track('signature', $sig->getKey());
            $this->track('file_local', $ttd);
            $this->track('file_local', $stempel);
        }
    }

    private function signaturePng(int $variant): string
    {
        $w = 560;
        $h = 220;
        $im = imagecreatetruecolor($w, $h);
        imagesavealpha($im, true);
        imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));
        $ink = imagecolorallocate($im, 20, 33, 110);
        $pts = [];
        // Huruf kapital berlekuk di awal
        for ($t = 0; $t <= 1; $t += 0.01) {
            $a = $t * 2 * M_PI;
            $pts[] = [70 + 38 * sin($a) * (1 - 0.3 * $t), 95 - 60 * cos($a) * (0.6 + 0.4 * $t)];
        }
        // Rangkaian huruf bersambung (trokoid miring)
        $loops = $variant === 1 ? 7 : 6;
        for ($t = 0; $t <= 1; $t += 0.0025) {
            $phase = 2 * M_PI * $loops * $t;
            $x = 95 + 360 * $t + (14 + 6 * sin($t * 7)) * sin($phase + 0.6);
            $amp = 26 * (0.65 + 0.35 * sin($t * 11 + $variant)) * (1 - 0.3 * $t);
            $y = 120 - $amp * cos($phase) - 18 * $t + 6 * sin($t * 9);
            $pts[] = [$x, $y];
        }
        // Coretan akhir ke kanan bawah lalu garis bawah melengkung
        for ($t = 0; $t <= 1; $t += 0.02) {
            $pts[] = [455 + 60 * $t, 100 + 55 * $t * $t];
        }
        for ($i = 1, $n = count($pts); $i < $n; $i++) {
            $thick = 3 + (int) round(1.5 * sin($i / 37));
            imagesetthickness($im, max(2, $thick));
            imageline($im, (int) $pts[$i - 1][0], (int) $pts[$i - 1][1], (int) $pts[$i][0], (int) $pts[$i][1], $ink);
        }
        imagesetthickness($im, 3);
        $prev = null;
        for ($t = 0; $t <= 1; $t += 0.02) {
            $p = [60 + 460 * $t, 178 - 14 * sin($t * M_PI)];
            if ($prev) {
                imageline($im, (int) $prev[0], (int) $prev[1], (int) $p[0], (int) $p[1], $ink);
            }
            $prev = $p;
        }
        ob_start();
        imagepng($im);
        imagedestroy($im);

        return (string) ob_get_clean();
    }

    private function stampPng(string $text): string
    {
        $s = 320;
        $c = $s / 2;
        $im = imagecreatetruecolor($s, $s);
        imagesavealpha($im, true);
        imageantialias($im, true);
        imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));
        $blue = imagecolorallocatealpha($im, 38, 56, 168, 18);
        // Lingkaran tebal: beberapa elips berdekatan (imageellipse tidak memakai ketebalan garis)
        foreach ([[300, 7], [236, 3]] as [$d, $w]) {
            for ($k = 0; $k < $w; $k++) {
                imageellipse($im, $c, $c, $d - $k, $d - $k, $blue);
            }
        }
        $font = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf');
        $ttf = is_file($font) && function_exists('imagettftext');
        $centerText = function (string $str, float $size, int $y) use ($im, $blue, $font, $ttf, $c) {
            if (! $ttf) {
                imagestring($im, 4, (int) ($c - imagefontwidth(4) * strlen($str) / 2), $y - 12, $str, $blue);

                return;
            }
            $box = imagettfbbox($size, 0, $font, $str);
            imagettftext($im, $size, 0, (int) ($c - ($box[2] - $box[0]) / 2), $y, $blue, $font, $str);
        };
        if ($ttf) {
            // Teks melingkar di bagian atas dan bawah cincin
            $arc = function (string $str, float $radius, float $startDeg, float $endDeg, bool $bottom) use ($im, $blue, $font, $c) {
                $chars = mb_str_split($str);
                $n = count($chars);
                foreach ($chars as $i => $ch) {
                    $deg = $startDeg + ($endDeg - $startDeg) * ($n === 1 ? 0.5 : $i / ($n - 1));
                    $rad = deg2rad($deg);
                    $x = $c + $radius * cos($rad);
                    $y = $c + $radius * sin($rad);
                    $angle = $bottom ? -($deg - 90) : -($deg + 90);
                    imagettftext($im, 15, $angle, (int) ($x - 5), (int) ($y + 6), $blue, $font, $ch);
                }
            };
            $arc('UNIVERSITAS ANDALAS', 128, 205, 335, false);
            $arc('PADANG', 132, 125, 55, true);
        }
        imagesetthickness($im, 2);
        imageline($im, 70, 136, 250, 136, $blue);
        imageline($im, 70, 186, 250, 186, $blue);
        $centerText('4th ASIC 2027', 20, 172);
        $centerText($text, 11, 124);
        $centerText('FAKULTAS PERTANIAN', 10, 210);
        ob_start();
        imagepng($im);
        imagedestroy($im);

        return (string) ob_get_clean();
    }

    // ---------------------------------------------------------------- template unduhan

    private function templates(Event $event): void
    {
        $docs = app(DocumentService::class);
        $items = [
            ['Abstrak', 'Template Abstrak 4th ASIC 2027', 'Abstract Template', [
                'Title (Calibri 14, bold, maximum 20 words)', 'Author names (underline the presenting author), affiliations, and e-mail of the corresponding author',
                'Abstract: maximum 250 words covering background, objective, method, results and conclusion', 'Keywords: maximum 5 words, separated by commas', 'Scope: choose one (CP, PB, PP, SM, SE, OT)',
            ]],
            ['Artikel', 'Template Full Paper 4th ASIC 2027', 'Full Paper Template', [
                'Title, authors and affiliations', 'Abstract and keywords', 'Introduction', 'Materials and Methods', 'Results and Discussion', 'Conclusion', 'Acknowledgements', 'References (APA style)',
            ]],
            ['Presentasi', 'Template Presentasi 4th ASIC 2027', 'Presentation Template', [
                'Slide 1: title, presenter, affiliation, abstract ID', 'Slide 2: background and objectives', 'Slides 3-6: methods and key results', 'Slide 7: conclusion and recommendations', 'Oral: 7 minutes presentation + 3 minutes discussion',
            ]],
            ['Panduan', 'Panduan Peserta 4th ASIC 2027', 'Participant Guide', [
                'Create an account and complete your profile', 'Register for the event and choose your participation type', 'Presenters submit the abstract and wait for the decision', 'Pay the invoice within 7 days and upload the transfer proof', 'Show your attendance QR at re-registration and record session attendance', 'Download your certificate after the conference',
            ]],
        ];
        foreach ($items as [$jenis, $nama, $title, $lines]) {
            if (DocumentTemplate::where('id_event', $event->id_event)->where('jenis_dokumen', $jenis)->where('status_template', 'Aktif')->exists()) {
                continue;
            }
            $html = '<html><head><meta charset="utf-8"><style>@page{margin:2.2cm}body{font-family:DejaVu Sans,sans-serif;font-size:11pt;color:#182820}h1{color:#0B5D2E;font-size:18pt;margin:0 0 4px}h2{font-size:11pt;color:#66746C;margin:0 0 18px;font-weight:normal}li{margin:0 0 8px}.box{border:1px solid #cfe6d6;background:#F3F9F5;padding:14px 18px;border-radius:6px}</style></head><body>'
                .'<h1>'.e($title).'</h1><h2>4th Agrifood System International Conference (ASIC) 2027 · Faculty of Agriculture, Universitas Andalas</h2>'
                .'<div class="box"><ol>'.collect($lines)->map(fn ($l) => '<li>'.e($l).'</li>')->implode('').'</ol></div></body></html>';
            $path = sprintf('templates/%d/%s/demo-%s.pdf', $event->id_event, strtolower($jenis), Str::slug($title));
            $pdf = Pdf::loadHTML($html)->setPaper('a4')->output();
            Storage::disk($docs->disk())->put($path, $pdf);
            $tpl = DocumentTemplate::create([
                'id_event' => $event->id_event, 'nama_template' => $nama, 'jenis_dokumen' => $jenis, 'file_template' => $path,
                'tanggal_upload' => now(), 'status_template' => 'Aktif', 'nama_file_asli' => Str::slug($title).'.pdf', 'mime' => 'application/pdf', 'ukuran_file' => strlen($pdf),
            ]);
            $this->track('template', $tpl->id_template);
            $this->track('file_docs', $path);
        }
    }

    // ---------------------------------------------------------------- sesi + QR

    private function sessions(Event $event): void
    {
        $start = Carbon::parse($event->tanggal_mulai);
        foreach ([['Keynote Session I', 9, 30, 12], ['Parallel Oral Session A', 13, 0, 15]] as [$name, $h, $m, $endH]) {
            $token = Str::random(40);
            $path = 'qr-codes/sessions/demo-'.Str::slug($name).'.svg';
            Storage::disk('public')->put($path, $this->qrSvg($token));
            $session = EventSession::create([
                'id_event' => $event->id_event, 'nama_sesi' => $name,
                'waktu' => $start->copy()->setTime($h, $m), 'waktu_selesai' => $start->copy()->setTime($endH, 0),
                'qr_token' => $token, 'qr_code_path' => $path,
            ]);
            $this->track('session', $session->getKey());
            $this->track('file_public', $path);
        }
    }

    private function qrSvg(string $token): string
    {
        if (class_exists(QrCode::class)) {
            try {
                return (string) QrCode::format('svg')->size(350)->errorCorrection('H')->generate($token);
            } catch (Throwable) {
            }
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" width="350" height="350"><rect width="350" height="350" fill="#fff"/><text x="175" y="180" font-size="12" text-anchor="middle">'.e($token).'</text></svg>';
    }

    // ---------------------------------------------------------------- peserta & dokumen resmi

    private function participants(Event $event): void
    {
        $period = RegistrationPeriod::where('id_event', $event->id_event)->where('status_periode', 'Aktif')->orderBy('tanggal_mulai')->first();
        if (! $period) {
            return;
        }
        $reviewer = UserAccount::whereHas('role', fn ($q) => $q->where('nama_role', Role::ROLE_TIM_ABSTRAK))->first();
        $treasurer = UserAccount::whereHas('role', fn ($q) => $q->where('nama_role', Role::ROLE_BENDAHARA))->first();
        $session = EventSession::whereIn('id_session', $this->created['session'] ?? [])->orderBy('waktu')->first();
        $people = [
            // [kunci, nama depan, nama belakang, institusi, negara, jenis, mode, scope, judul, alur]
            ['complete', 'Rina', 'Wulandari, M.Si.', 'Universitas Andalas', 'Indonesia', 'Oral Presenter', 'Luring', 'CP', 'Effect of Biochar on Shallot Growth in Lowland Peat Soil', 'complete'],
            ['review', 'Ahmad', 'Fauzi, S.P.', 'Universitas Riau', 'Indonesia', 'Poster Presenter', 'Luring', 'PP', 'Botanical Insecticides for Controlling Fall Armyworm on Maize', 'review'],
            ['rejected', 'Nguyen', 'Van Minh', 'Hue University', 'Vietnam', 'Oral Presenter', 'Daring', 'SE', 'Farmer Adoption of Mobile Weather Advisories in Central Vietnam', 'rejected'],
            ['listener', 'Siti', 'Rahmah', 'Universitas Negeri Padang', 'Indonesia', 'Listener', 'Daring', null, null, 'awaiting'],
        ];
        foreach ($people as [$key, $first, $last, $inst, $country, $type, $mode, $scope, $title, $flow]) {
            try {
                DB::transaction(function () use ($event, $period, $reviewer, $treasurer, $session, $key, $first, $last, $inst, $country, $type, $mode, $scope, $title, $flow) {
                    $email = 'peserta.'.$key.'@demo.asic.test';
                    if (UserAccount::where('email', $email)->exists()) {
                        return;
                    }
                    $role = Role::where('nama_role', Role::ROLE_PESERTA)->firstOrFail();
                    $user = UserAccount::create(['id_role' => $role->id_role, 'email' => $email, 'password' => Hash::make(Str::random(24)), 'status_akun' => 'Aktif']);
                    $this->track('user', $user->id_user);
                    $participant = Participant::create(['id_user' => $user->id_user, 'first_name' => $first, 'last_name' => $last, 'institution' => $inst, 'country' => $country, 'job_title' => 'Lecturer']);
                    $reg = Registration::create([
                        'id_participant' => $participant->id_participant, 'id_event' => $event->id_event, 'id_period' => $period->id_period,
                        'registration_type' => 'Regular', 'attendance_mode' => $mode, 'participation_type' => $type,
                        'publication_option' => $type === 'Listener' ? 'Tidak' : 'Prosiding', 'registration_status' => 'Terdaftar',
                    ]);
                    $this->track('registration', $reg->id_registration);

                    if ($scope) {
                        $file = 'abstracts/demo-'.$key.'.pdf';
                        Storage::disk('local')->put($file, Pdf::loadHTML('<h2 style="font-family:DejaVu Sans">'.e($title).'</h2><p style="font-family:DejaVu Sans">'.e($first.' '.$last).' · '.e($inst).'</p><p style="font-family:DejaVu Sans;text-align:justify">'.str_repeat('This study examines practical approaches to sustainable agrifood systems in tropical regions. ', 8).'</p><p style="font-family:DejaVu Sans"><b>Keywords:</b> smart farming, sustainability, tropical agriculture</p>')->setPaper('a4')->output());
                        $this->track('file_local', $file);
                        $abstract = AbstractSubmission::create([
                            'id_registration' => $reg->id_registration, 'judul_abstrak' => $title, 'scope' => $scope,
                            'metode_presentasi' => $type === 'Poster Presenter' ? 'Poster' : 'Oral', 'file_abstrak' => $file,
                            'tanggal_submit' => now()->subDays(3), 'status_abstrak' => 'Diajukan',
                        ]);
                        if (in_array($flow, ['complete', 'rejected'], true) && $reviewer) {
                            $decision = $flow === 'complete' ? 'Diterima' : 'Ditolak';
                            AbstractReview::create([
                                'id_abstract' => $abstract->id_abstract, 'id_reviewer' => $reviewer->id_user, 'tanggal_review' => now()->subDays(2),
                                'hasil_review' => $decision, 'catatan_review' => $decision === 'Diterima' ? 'Relevant to the scope with clear methods and results.' : 'The topic is outside the conference scope and the method is not described clearly.',
                            ]);
                            $abstract->update(['status_abstrak' => $decision]);
                            DB::afterCommit(fn () => app(AbstractDecisionProcessor::class)->process($abstract->fresh()));
                        }
                    }
                    if ($flow === 'complete') {
                        DB::afterCommit(fn () => $this->completeFlow($reg->fresh(), $treasurer, $session));
                    }
                    if ($flow === 'awaiting') {
                        DB::afterCommit(fn () => $this->uploadProof($reg->fresh()));
                    }
                });
            } catch (Throwable $e) {
                report($e);
            }
        }
    }

    /** Bayar → verifikasi bendahara → receipt → registrasi ulang → hadir & presentasi → sertifikat. */
    private function completeFlow(Registration $reg, ?UserAccount $treasurer, ?EventSession $session): void
    {
        $proof = $this->uploadProof($reg);
        $invoice = Invoice::where('id_registration', $reg->id_registration)->first();
        if (! $proof || ! $invoice || ! $treasurer) {
            return;
        }
        $verification = PaymentVerification::create([
            'id_payment_proof' => $proof->id_payment_proof, 'id_treasurer' => $treasurer->id_user, 'tanggal_verifikasi' => now()->subDay(),
            'hasil_verifikasi' => 'Valid', 'alasan_penolakan' => null,
        ]);
        $invoice->update(['status_pembayaran' => 'Terverifikasi']);
        $docs = app(DocumentService::class);
        $docs->issue($reg, 'Receipt', [
            'invoice_nomor' => $invoice->nomor_invoice, 'amount' => $invoice->total_tagihan, 'currency' => $invoice->mata_uang ?? 'IDR',
            'verified_at' => $verification->tanggal_verifikasi,
            'items' => $invoice->invoiceDetails()->get(['jenis_biaya', 'deskripsi', 'jumlah', 'harga_satuan', 'subtotal'])->toArray(),
        ]);
        $reg->update(['reregistered_at' => now()->subHours(3), 'reregistered_by' => $treasurer->id_user]);
        if ($session) {
            Attendance::create([
                'id_registration' => $reg->id_registration, 'id_session' => $session->id_session, 'tanggal_generate' => now()->subHours(3),
                'tanggal_check_in' => now()->subHours(2), 'status_kehadiran' => 'Hadir', 'status_presentasi' => 'Sudah',
            ]);
        }
        $docs->issue($reg->fresh(), 'Sertifikat', ['certificate_lang' => 'id']);
    }

    private function uploadProof(Registration $reg): ?PaymentProof
    {
        $invoice = Invoice::where('id_registration', $reg->id_registration)->first();
        if (! $invoice) {
            return null;
        }
        $path = 'payment-proofs/'.$invoice->id_invoice.'/demo-transfer.png';
        Storage::disk('local')->put($path, $this->transferPng((float) $invoice->total_tagihan, $invoice->mata_uang ?? 'IDR', $invoice->nomor_invoice));
        $this->track('file_local', $path);
        $proof = PaymentProof::create([
            'id_invoice' => $invoice->id_invoice, 'file_bukti' => $path, 'tanggal_upload' => now()->subDays(1),
            'nominal_transfer' => $invoice->total_tagihan, 'catatan_peserta' => 'Transfer via mobile banking',
        ]);
        $invoice->update(['status_pembayaran' => 'Menunggu Verifikasi']);

        return $proof;
    }

    private function transferPng(float $amount, string $currency, string $invoiceNo): string
    {
        $im = imagecreatetruecolor(420, 300);
        $bg = imagecolorallocate($im, 255, 255, 255);
        $green = imagecolorallocate($im, 11, 93, 46);
        $dark = imagecolorallocate($im, 24, 40, 32);
        $muted = imagecolorallocate($im, 102, 116, 108);
        imagefill($im, 0, 0, $bg);
        imagefilledrectangle($im, 0, 0, 420, 56, $green);
        imagestring($im, 5, 20, 20, 'TRANSFER BERHASIL', $bg);
        $lines = [['Tanggal', now()->format('d/m/Y H:i')], ['Ke rekening', 'Panitia 4th ASIC 2027'], ['Berita', $invoiceNo], ['Nominal', Currency::format($amount, $currency)], ['Status', 'Sukses']];
        foreach ($lines as $i => [$k, $v]) {
            imagestring($im, 3, 20, 80 + $i * 38, $k, $muted);
            imagestring($im, 4, 150, 78 + $i * 38, $v, $dark);
        }
        ob_start();
        imagepng($im);
        imagedestroy($im);

        return (string) ob_get_clean();
    }

    // ---------------------------------------------------------------- konten lain

    private function announcement(Event $event): void
    {
        $author = UserAccount::whereHas('role', fn ($q) => $q->where('nama_role', Role::ROLE_SEKRETARIAT))->first()
            ?? UserAccount::whereHas('role', fn ($q) => $q->where('nama_role', Role::ROLE_ADMIN))->first();
        if (! $author) {
            return;
        }
        $a = Announcement::create([
            'id_event' => $event->id_event, 'id_author' => $author->id_user, 'judul' => 'Abstract submission is now open',
            'isi_pengumuman' => "Pengiriman abstrak 4th ASIC 2027 telah dibuka. Silakan buat akun, lengkapi profil, lalu kirim abstrak sesuai salah satu dari enam ruang lingkup.\n\nAbstract submission for the 4th ASIC 2027 is now open.",
            'tanggal_publikasi' => now(), 'status' => 'Dipublikasikan',
        ]);
        $this->track('announcement', $a->getKey());
    }

    private function legal(Event $event): void
    {
        $docs = [
            'terms' => ['Syarat & Ketentuan Peserta', 'Participant Terms & Conditions',
                "1. Peserta wajib mengisi data diri dengan benar.\n2. Abstrak harus karya asli penulis dan sesuai ruang lingkup konferensi.\n3. Pembayaran dilakukan paling lambat 7 hari setelah invoice terbit; harga early bird berlaku selama periode early bird.\n4. Biaya seminar tidak dapat dikembalikan; pengembalian biaya publikasi diproses manual oleh panitia.\n5. Sertifikat diberikan kepada peserta yang mengisi absensi; pemakalah juga wajib presentasi sesuai jadwal.",
                "1. Participants must provide accurate personal data.\n2. Abstracts must be the authors' original work and fit the conference scope.\n3. Payment is due within 7 days of the invoice; early bird prices apply only during the early bird period.\n4. Seminar fees are non-refundable; publication fee refunds are handled manually by the committee.\n5. Certificates are issued to participants who record attendance; presenters must also present as scheduled."],
            'privacy' => ['Kebijakan Privasi', 'Privacy Policy',
                'Panitia mengumpulkan nama, email, institusi, negara, nomor telepon, berkas abstrak/makalah, dan bukti pembayaran untuk keperluan pendaftaran, seleksi, pembayaran, penerbitan dokumen, dan sertifikat 4th ASIC 2027. Data hanya diakses panitia sesuai perannya dan tidak dijual kepada pihak lain. Peserta dapat meminta perbaikan atau penghapusan data melalui sekretariat.',
                'The committee collects name, e-mail, institution, country, phone number, abstract/paper files and payment proofs for registration, review, payment, document and certificate issuance for the 4th ASIC 2027. Data is only accessed by committee members according to their role and is never sold. Participants may request correction or deletion through the secretariat.'],
            'ethics' => ['Etika Publikasi', 'Publication Ethics',
                'Penulis wajib menghindari plagiarisme, fabrikasi, dan falsifikasi data, serta mencantumkan seluruh kontributor. Naskah ditelaah secara double blind oleh minimal dua penelaah. Pelanggaran etika dapat menyebabkan naskah ditolak atau ditarik.',
                'Authors must avoid plagiarism, fabrication and falsification of data and acknowledge all contributors. Manuscripts undergo double-blind review by at least two reviewers. Ethical violations may lead to rejection or retraction.'],
        ];
        foreach ($docs as $jenis => [$tId, $tEn, $iId, $iEn]) {
            if (LegalDocument::where('jenis', $jenis)->where('status', 'Dipublikasikan')->exists()) {
                continue;
            }
            $doc = LegalDocument::create(['jenis' => $jenis, 'versi' => '1.0', 'judul_id' => $tId, 'judul_en' => $tEn, 'isi_id' => $iId, 'isi_en' => $iEn, 'berlaku_mulai' => now()->toDateString(), 'status' => 'Dipublikasikan']);
            $this->track('legal', $doc->getKey());
        }
    }

    private function photos(Event $event): void
    {
        foreach ([['conference-hall', 'Sesi pleno konferensi', 'Plenary session'], ['smart-farming-lab', 'Riset pertanian cerdas', 'Smart farming research'], ['farmers-planting', 'Kunjungan lapangan', 'Field visit']] as $i => [$file, $capId, $capEn]) {
            $src = public_path('images/asic/photos/'.$file.'.jpg');
            if (! is_file($src)) {
                continue;
            }
            $path = 'event-photos/demo-'.$file.'.jpg';
            Storage::disk('public')->put($path, file_get_contents($src));
            $photo = EventPhoto::create(['id_event' => $event->id_event, 'file_path' => $path, 'caption_id' => $capId, 'caption_en' => $capEn, 'status' => 'Dipublikasikan', 'urutan' => $i]);
            $this->track('photo', $photo->getKey());
            $this->track('file_public', $path);
        }
    }

    private function inquiry(Event $event): void
    {
        $q = SponsorshipInquiry::create([
            'id_event' => $event->id_event, 'jenis' => 'Sponsorship', 'nama_organisasi' => 'PT Tani Maju Sejahtera', 'nama_kontak' => 'Budi Santoso',
            'email' => 'budi@tanimaju.example', 'telepon' => '0812-0000-0000', 'pesan' => 'Kami tertarik menjadi sponsor Gold dan membuka booth pameran alat pertanian.', 'status' => 'Baru',
        ]);
        $this->track('inquiry', $q->getKey());
    }

    private function archive(Event $event): void
    {
        if (Event::where('nama_event', '3rd ASIC 2024')->exists()) {
            return;
        }
        $old = Event::create([
            'nama_event' => '3rd ASIC 2024', 'tema' => 'Sustainable Agrifood Systems for Climate Resilience', 'deskripsi' => 'The 3rd Agrifood System International Conference',
            'tanggal_mulai' => '2024-08-21', 'tanggal_selesai' => '2024-08-22', 'lokasi' => 'Universitas Andalas, Padang', 'mode_kegiatan' => 'Hybrid', 'status_event' => 'Arsip',
        ]);
        $this->track('archive_event', $old->id_event);
    }

    // ---------------------------------------------------------------- penghapusan

    /** @param  array<string, list<mixed>>  $created */
    public static function remove(array $created): void
    {
        $regIds = $created['registration'] ?? [];
        if ($regIds) {
            $invoiceIds = Invoice::whereIn('id_registration', $regIds)->pluck('id_invoice');
            $proofIds = PaymentProof::whereIn('id_invoice', $invoiceIds)->pluck('id_payment_proof');
            foreach (PaymentProof::whereIn('id_payment_proof', $proofIds)->pluck('file_bukti') as $f) {
                Storage::disk('local')->delete($f);
            }
            PaymentVerification::whereIn('id_payment_proof', $proofIds)->delete();
            PaymentProof::whereIn('id_payment_proof', $proofIds)->delete();
            $disk = app(DocumentService::class)->disk();
            foreach (IssuedDocument::whereIn('id_registration', $regIds)->pluck('file_document') as $f) {
                Storage::disk($disk)->delete($f);
            }
            IssuedDocument::whereIn('id_registration', $regIds)->delete();
            Invoice::whereIn('id_invoice', $invoiceIds)->get()->each(function (Invoice $i) {
                $i->invoiceDetails()->delete();
                $i->delete();
            });
            Attendance::whereIn('id_registration', $regIds)->delete();
            $abstractIds = AbstractSubmission::whereIn('id_registration', $regIds)->pluck('id_abstract');
            AbstractReview::whereIn('id_abstract', $abstractIds)->delete();
            AbstractSubmission::whereIn('id_abstract', $abstractIds)->delete();
            DB::table('participant_file')->whereIn('id_registration', $regIds)->delete();
            DB::table('registration_add_on')->whereIn('id_registration', $regIds)->delete();
            Registration::whereIn('id_registration', $regIds)->delete();
        }
        if ($userIds = $created['user'] ?? []) {
            DB::table('notifications')->where('notifiable_type', UserAccount::class)->whereIn('notifiable_id', $userIds)->delete();
            UserAccount::whereIn('id_user', $userIds)->get()->each->delete();
        }
        Attendance::whereIn('id_session', $created['session'] ?? [])->delete();
        EventSession::whereIn('id_session', $created['session'] ?? [])->delete();
        DigitalSignature::whereIn('id_signature', $created['signature'] ?? [])->delete();
        DocumentTemplate::whereIn('id_template', $created['template'] ?? [])->delete();
        Announcement::whereKey($created['announcement'] ?? [])->delete();
        LegalDocument::whereKey($created['legal'] ?? [])->delete();
        EventPhoto::whereKey($created['photo'] ?? [])->delete();
        SponsorshipInquiry::whereKey($created['inquiry'] ?? [])->delete();
        Event::whereKey($created['archive_event'] ?? [])->delete();
        foreach ($created['file_local'] ?? [] as $f) {
            Storage::disk('local')->delete($f);
        }
        foreach ($created['file_public'] ?? [] as $f) {
            Storage::disk('public')->delete($f);
        }
        foreach ($created['file_docs'] ?? [] as $f) {
            Storage::disk(app(DocumentService::class)->disk())->delete($f);
        }
    }
}
