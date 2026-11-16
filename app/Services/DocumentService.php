<?php

namespace App\Services;

use App\Models\DocumentTemplate;
use App\Models\Event;
use App\Models\IssuedDocument;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class DocumentService
{
    public const DEFAULT_FORMATS = [
        // Mengikuti template panitia. Invoice di template hanya "001"; diberi akhiran
        // INV/edisi/tahun karena nomor_invoice wajib unik di database.
        'LOA' => '{SEQ}/{SCOPE}/{EDITION} ASIC/{YYYY}',
        'Penolakan' => '{SEQ}/{EDITION} ASIC/{YYYY}',
        'Invoice' => '{SEQ}/INV/{EDITION} ASIC/{YYYY}',
        'Receipt' => '{SEQ}/{SCOPE}/{EDITION} ASIC/{YYYY}',
        'Sertifikat' => '{SEQ}/CERT/{EDITION} ASIC/{YYYY}',
    ];

    public function __construct(private DocumentRenderer $renderer) {}

    public function disk(): string
    {
        return config('asic.documents_disk', 'local');
    }

    /**
     * Template aktif milik event; jika belum ada, dibuatkan template bawaan (builtin)
     * agar penerbitan tidak terblokir sebelum berkas resmi client diunggah.
     */
    public function resolveTemplate(int $eventId, string $jenis): DocumentTemplate
    {
        $template = DocumentTemplate::where('id_event', $eventId)
            ->where('jenis_dokumen', $jenis)
            ->where('status_template', 'Aktif')
            ->orderByDesc('id_template')
            ->first();

        return $template ?? DocumentTemplate::create([
            'id_event' => $eventId,
            'nama_template' => "Template Bawaan {$jenis}",
            'jenis_dokumen' => $jenis,
            'format_nomor' => self::DEFAULT_FORMATS[$jenis] ?? null,
            'file_template' => 'builtin:'.strtolower($jenis),
            'tanggal_upload' => now(),
            'status_template' => 'Aktif',
        ]);
    }

    /**
     * Nomor surat mengikuti template panitia, mis. 001/CP/4th ASIC/2027.
     * {SEQ} = urutan 3 digit per jenis dokumen per event (tidak lompat-lompat),
     * {SCOPE} = kode scope abstrak, atau "L" untuk Listener,
     * {EDITION} = edisi seminar (config asic.edition), {YYYY} = tahun pelaksanaan event.
     */
    public function nextNumber(DocumentTemplate $template, string $jenis, ?Registration $reg = null): string
    {
        $eventId = $reg?->id_event ?? $template->id_event;
        $seq = IssuedDocument::where('jenis_dokumen', $jenis)
            ->whereHas('registration', fn ($q) => $q->where('id_event', $eventId))
            ->lockForUpdate()
            ->count() + 1;
        $format = $template->format_nomor ?: self::DEFAULT_FORMATS[$jenis];

        $event = $reg?->event ?? Event::find($eventId);
        $year = $event?->tanggal_mulai ? Carbon::parse($event->tanggal_mulai)->format('Y') : now()->format('Y');

        return str_replace(
            ['{SEQ}', '{SCOPE}', '{EDITION}', '{YYYY}', '{MM}'],
            [str_pad((string) $seq, 3, '0', STR_PAD_LEFT), $this->scopeCode($reg), config('asic.edition', '4th'), $year, now()->format('m')],
            $format
        );
    }

    /**
     * Kode scope untuk nomor surat. Listener selalu "L". Scope boleh tersimpan sebagai kode (CP)
     * atau nama lengkap (Crop Production). Scope tak dikenal → "XX" agar mudah terlihat saat QA.
     */
    public function scopeCode(?Registration $reg): string
    {
        if (! $reg) {
            return 'XX';
        }
        if (! $reg->isPresenter()) {
            return 'L';
        }
        $scope = trim((string) $reg->abstractSubmission?->scope);
        $codes = config('asic.scope_codes', []);
        if (isset($codes[strtoupper($scope)])) {
            return strtoupper($scope);
        }
        foreach ($codes as $code => $name) {
            if (strcasecmp($name, $scope) === 0) {
                return $code;
            }
        }

        return 'XX';
    }

    /**
     * Data registrasi/peserta/abstrak/event yang disisipkan ke template.
     *
     * @return array<string, mixed>
     */
    public function baseContext(Registration $reg): array
    {
        $reg->loadMissing(['event', 'registrationPeriod', 'participant.userAccount', 'abstractSubmission.latestReview']);
        $p = $reg->participant;
        $e = $reg->event;
        $a = $reg->abstractSubmission;

        return [
            'name' => trim($p?->full_name) ?: ($p?->userAccount?->email ?? '-'),
            'email' => $p?->userAccount?->email,
            'institution' => $p?->institution,
            'country' => $p?->country,
            'event_name' => $e->nama_event,
            'event_theme' => $e->tema,
            'period_name' => $reg->registrationPeriod?->nama_periode,
            'publication_option' => $reg->publication_option,
            'is_presenter' => $reg->isPresenter(),
            'event_dates' => $e->tanggal_mulai && $e->tanggal_selesai
                ? Carbon::parse($e->tanggal_mulai)->format('d F Y').' - '.Carbon::parse($e->tanggal_selesai)->format('d F Y') : null,
            'event_location' => $e->lokasi,
            'registration_type' => $reg->registration_type,
            'attendance_mode' => $reg->attendance_mode,
            'participation_type' => $reg->participation_type,
            'abstract_title' => $a?->judul_abstrak,
            'presentation_method' => $a?->metode_presentasi,
            'review_notes' => $a?->latestReview?->catatan_review,
        ];
    }

    /**
     * Terbitkan dokumen. Idempoten: satu dokumen per (registrasi, jenis); pemanggilan
     * ulang mengembalikan dokumen yang sudah ada sehingga tidak ada dokumen ganda.
     *
     * @param  array<string, mixed>  $extra
     */
    public function issue(Registration $reg, string $jenis, array $extra = [], ?string $nomor = null): IssuedDocument
    {
        return DB::transaction(function () use ($reg, $jenis, $extra, $nomor) {
            Registration::whereKey($reg->getKey())->lockForUpdate()->first();

            $existing = IssuedDocument::where('id_registration', $reg->id_registration)
                ->where('jenis_dokumen', $jenis)->first();
            if ($existing) {
                return $existing;
            }

            $template = $this->resolveTemplate($reg->id_event, $jenis);
            $nomor ??= $this->nextNumber($template, $jenis, $reg);
            $context = array_merge($this->baseContext($reg), $extra, ['nomor' => $nomor, 'tanggal' => now()]);

            $path = sprintf('documents/%s/%d-%s.pdf', strtolower($jenis), $reg->id_registration, Str::slug(str_replace('/', '-', $nomor)));
            Storage::disk($this->disk())->put($path, $this->renderer->render($jenis, $context));

            try {
                $document = IssuedDocument::create([
                    'id_registration' => $reg->id_registration,
                    'id_template' => $template->id_template,
                    'jenis_dokumen' => $jenis,
                    'nomor_dokumen' => $nomor,
                    'file_document' => $path,
                    'tanggal_terbit' => now(),
                ]);
            } catch (Throwable $e) {
                Storage::disk($this->disk())->delete($path);
                throw $e;
            }

            // KF-30: satu titik notifikasi untuk dokumen terbit (dikirim setelah commit, gagal tidak membatalkan).
            if ($kind = StatusNotifier::documentKind($jenis)) {
                app(StatusNotifier::class)->notify($reg, $kind, 'document:'.$document->id_document);
            }

            return $document;
        });
    }
}
