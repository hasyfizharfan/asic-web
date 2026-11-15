<?php

namespace App\Services;

use App\Models\DocumentTemplate;
use App\Models\IssuedDocument;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class DocumentService
{
    public const DEFAULT_FORMATS = [
        'LOA' => 'LOA/{SEQ}/ASIC/{YYYY}',
        'Penolakan' => 'REJ/{SEQ}/ASIC/{YYYY}',
        'Invoice' => 'INV/{SEQ}/ASIC/{YYYY}',
        'Receipt' => 'RCP/{SEQ}/ASIC/{YYYY}',
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

    public function nextNumber(DocumentTemplate $template, string $jenis): string
    {
        $seq = ((int) IssuedDocument::where('jenis_dokumen', $jenis)->max('id_document')) + 1;
        $format = $template->format_nomor ?: self::DEFAULT_FORMATS[$jenis];

        return str_replace(
            ['{SEQ}', '{YYYY}', '{MM}'],
            [str_pad((string) $seq, 4, '0', STR_PAD_LEFT), now()->format('Y'), now()->format('m')],
            $format
        );
    }

    /**
     * Data registrasi/peserta/abstrak/event yang disisipkan ke template.
     *
     * @return array<string, mixed>
     */
    public function baseContext(Registration $reg): array
    {
        $reg->loadMissing(['event', 'participant.userAccount', 'abstractSubmission.latestReview']);
        $p = $reg->participant;
        $e = $reg->event;
        $a = $reg->abstractSubmission;

        return [
            'name' => trim($p?->full_name) ?: ($p?->userAccount?->email ?? '-'),
            'institution' => $p?->institution,
            'country' => $p?->country,
            'event_name' => $e->nama_event,
            'event_dates' => $e->tanggal_mulai && $e->tanggal_selesai
                ? \Carbon\Carbon::parse($e->tanggal_mulai)->format('d F Y').' - '.\Carbon\Carbon::parse($e->tanggal_selesai)->format('d F Y') : null,
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
            $nomor ??= $this->nextNumber($template, $jenis);
            $context = array_merge($this->baseContext($reg), $extra, ['nomor' => $nomor, 'tanggal' => now()]);

            $path = sprintf('documents/%s/%d-%s.pdf', strtolower($jenis), $reg->id_registration, Str::slug(str_replace('/', '-', $nomor)));
            Storage::disk($this->disk())->put($path, $this->renderer->render($jenis, $context));

            try {
                return IssuedDocument::create([
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
        });
    }
}
