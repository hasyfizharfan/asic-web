<?php

namespace App\Services;

use App\Models\AbstractSubmission;
use App\Models\Invoice;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Sumber tunggal angka rekapitulasi (M-10) dan baris ekspor Excel.
 * Seluruh angka dihitung langsung dari database; filter: id_event, id_period, status.
 *
 * @phpstan-type Filters array{id_event?: int|null, id_period?: int|null, status?: string|null}
 */
class ReportService
{
    private function regFilter(Builder $q, array $f, string $statusColumn = 'registration_status', bool $viaRegistration = false): Builder
    {
        $apply = function (Builder $b) use ($f) {
            // Kolom dikualifikasi nama tabel agar tidak ambigu ketika query di-join ke registration_period.
            $table = $b->getModel()->getTable();
            $b->when(! empty($f['id_event']), fn ($x) => $x->where("$table.id_event", $f['id_event']))
                ->when(! empty($f['id_period']), fn ($x) => $x->where("$table.id_period", $f['id_period']));
        };
        if ($viaRegistration) {
            $q->whereHas('registration', $apply);
        } else {
            $apply($q);
        }

        return $q;
    }

    private function money(float|int|string|null $v): string
    {
        return number_format((float) $v, 2, '.', '');
    }

    /** @return array<int, array{key: string|null, total: int}> */
    private function countBy(Builder $q, string $column): array
    {
        return $q->clone()->select($column.' as k', DB::raw('count(*) as total'))
            ->groupBy($column)->orderBy($column)->get()
            ->map(fn ($r) => ['key' => $r->k, 'total' => (int) $r->total])->all();
    }

    // ------------------------------------------------------------------ rekap

    public function registrations(array $f): array
    {
        $q = $this->regFilter(Registration::query(), $f);
        $q->when(! empty($f['status']), fn ($x) => $x->where('registration.registration_status', $f['status']));

        $byPeriod = $q->clone()->join('registration_period as p', 'p.id_period', '=', 'registration.id_period')
            ->select('p.nama_periode as k', DB::raw('count(*) as total'))->groupBy('p.nama_periode')->orderBy('p.nama_periode')->get()
            ->map(fn ($r) => ['key' => $r->k, 'total' => (int) $r->total])->all();

        return [
            'total' => $q->clone()->count(),
            'by_status' => $this->countBy($q, 'registration_status'),
            'by_registration_type' => $this->countBy($q, 'registration_type'),
            'by_attendance_mode' => $this->countBy($q, 'attendance_mode'),
            'by_participation_type' => $this->countBy($q, 'participation_type'),
            'by_publication_option' => $this->countBy($q, 'publication_option'),
            'by_period' => $byPeriod,
        ];
    }

    public function participants(array $f): array
    {
        $q = $this->regFilter(Registration::query(), $f);
        $q->when(! empty($f['status']), fn ($x) => $x->where('registration.registration_status', $f['status']));

        $matrix = $q->clone()->join('registration_period as p', 'p.id_period', '=', 'registration.id_period')
            ->select('p.nama_periode as period', 'registration.registration_type as type', DB::raw('count(*) as total'))
            ->groupBy('p.nama_periode', 'registration.registration_type')->orderBy('p.nama_periode')->orderBy('registration.registration_type')->get()
            ->map(fn ($r) => ['period' => $r->period, 'registration_type' => $r->type, 'total' => (int) $r->total])->all();

        $byCountry = $q->clone()->join('participant as pt', 'pt.id_participant', '=', 'registration.id_participant')
            ->select(DB::raw("coalesce(pt.country, 'Tidak diketahui') as k"), DB::raw('count(*) as total'))
            ->groupBy('k')->orderByDesc('total')->orderBy('k')->get()
            ->map(fn ($r) => ['key' => $r->k, 'total' => (int) $r->total])->all();

        return [
            'total_participants' => $q->clone()->distinct('registration.id_participant')->count('registration.id_participant'),
            'by_category_and_period' => $matrix,
            'by_country' => $byCountry,
        ];
    }

    public function abstracts(array $f): array
    {
        $q = $this->regFilter(AbstractSubmission::query(), $f, viaRegistration: true);
        $q->when(! empty($f['status']), fn ($x) => $x->where('status_abstrak', $f['status']));

        $byStatus = collect($this->countBy($q, 'status_abstrak'))->pluck('total', 'key');
        $accepted = (int) ($byStatus['Diterima'] ?? 0);
        $rejected = (int) ($byStatus['Ditolak'] ?? 0);
        $decided = $accepted + $rejected;

        return [
            'total' => $q->clone()->count(),
            'by_status' => $this->countBy($q, 'status_abstrak'),
            'by_presentation_method' => $this->countBy($q, 'metode_presentasi'),
            'by_scope' => $this->countBy($q, 'scope'),
            'selection' => [
                'diterima' => $accepted,
                'ditolak' => $rejected,
                'menunggu_seleksi' => (int) ($byStatus['Diajukan'] ?? 0),
                'acceptance_rate_percent' => $decided > 0 ? round($accepted / $decided * 100, 2) : null,
            ],
            'reviews_total' => DB::table('abstract_review')->whereIn('id_abstract', $q->clone()->select('id_abstract'))->count(),
        ];
    }

    public function invoices(array $f): array
    {
        $q = $this->regFilter(Invoice::query(), $f, viaRegistration: true);
        $q->when(! empty($f['status']), fn ($x) => $x->where('status_pembayaran', $f['status']));

        $byStatus = $q->clone()->select('status_pembayaran as k', DB::raw('count(*) as total'), DB::raw('coalesce(sum(total_tagihan),0) as amount'))
            ->groupBy('status_pembayaran')->orderBy('status_pembayaran')->get()
            ->map(fn ($r) => ['key' => $r->k, 'total' => (int) $r->total, 'total_tagihan' => $this->money($r->amount)])->all();

        $byCategory = $q->clone()->join('registration as r', 'r.id_registration', '=', 'invoice.id_registration')
            ->join('registration_period as p', 'p.id_period', '=', 'r.id_period')
            ->select('p.nama_periode as period', 'r.registration_type as type', DB::raw('count(*) as total'), DB::raw('coalesce(sum(invoice.total_tagihan),0) as amount'))
            ->groupBy('p.nama_periode', 'r.registration_type')->orderBy('p.nama_periode')->orderBy('r.registration_type')->get()
            ->map(fn ($r) => ['period' => $r->period, 'registration_type' => $r->type, 'total' => (int) $r->total, 'total_tagihan' => $this->money($r->amount)])->all();

        return [
            'total' => $q->clone()->count(),
            'total_tagihan' => $this->money($q->clone()->sum('total_tagihan')),
            'by_status' => $byStatus,
            'by_category_and_period' => $byCategory,
        ];
    }

    public function payments(array $f): array
    {
        $invoices = $this->regFilter(Invoice::query(), $f, viaRegistration: true);
        $proofs = DB::table('payment_proof')->whereIn('id_invoice', $invoices->clone()->select('id_invoice'));
        $verifs = DB::table('payment_verification')->whereIn('id_payment_proof', $proofs->clone()->select('id_payment_proof'));

        $paid = $invoices->clone()->where('status_pembayaran', 'Terverifikasi')->sum('total_tagihan');
        $outstanding = $invoices->clone()->whereIn('status_pembayaran', ['Menunggu Pembayaran', 'Menunggu Verifikasi', 'Ditolak'])->sum('total_tagihan');

        return [
            'proofs_total' => $proofs->clone()->count(),
            'verified_valid' => $verifs->clone()->where('hasil_verifikasi', 'Valid')->count(),
            'verified_invalid' => $verifs->clone()->where('hasil_verifikasi', 'Tidak Valid')->count(),
            'awaiting_verification' => $invoices->clone()->where('status_pembayaran', 'Menunggu Verifikasi')->count(),
            'by_invoice_status' => $this->countBy($invoices, 'status_pembayaran'),
            'amount_verified' => $this->money($paid),
            'amount_outstanding' => $this->money($outstanding),
        ];
    }

    // ----------------------------------------------------------------- ekspor

    /** @return array{0: array<int, string>, 1: array<int, array<int, mixed>>} */
    public function participantRows(array $f): array
    {
        $q = $this->regFilter(Registration::with(['participant.userAccount', 'event', 'registrationPeriod', 'invoice']), $f);
        $q->when(! empty($f['status']), fn ($x) => $x->where('registration.registration_status', $f['status']));

        $rows = $q->orderBy('id_registration')->get()->map(fn (Registration $r) => [
            $r->id_registration, $r->participant?->full_name, $r->participant?->userAccount?->email, $r->participant?->institution,
            $r->participant?->country, $r->participant?->phone, $r->event?->nama_event, $r->registrationPeriod?->nama_periode,
            $r->registration_type, $r->attendance_mode, $r->participation_type, $r->publication_option,
            $r->registration_status, (string) $r->registration_date, $r->invoice?->status_pembayaran ?? '-',
        ])->all();

        return [['ID Registrasi', 'Nama', 'Email', 'Instansi', 'Negara', 'Telepon', 'Event', 'Periode', 'Kategori', 'Mode Kehadiran',
            'Jenis Partisipasi', 'Opsi Publikasi', 'Status Registrasi', 'Tanggal Registrasi', 'Status Pembayaran'], $rows];
    }

    public function abstractRows(array $f): array
    {
        $q = $this->regFilter(AbstractSubmission::with(['registration.participant.userAccount', 'registration.event', 'latestReview.reviewer']), $f, viaRegistration: true);
        $q->when(! empty($f['status']), fn ($x) => $x->where('status_abstrak', $f['status']));

        $rows = $q->orderBy('id_abstract')->get()->map(fn (AbstractSubmission $a) => [
            $a->id_abstract, $a->judul_abstrak, $a->scope, $a->metode_presentasi, $a->status_abstrak, (string) $a->tanggal_submit,
            $a->registration?->participant?->full_name, $a->registration?->participant?->userAccount?->email,
            $a->registration?->participant?->institution, $a->registration?->event?->nama_event,
            $a->latestReview?->hasil_review, $a->latestReview?->catatan_review, $a->latestReview?->reviewer?->email, (string) $a->latestReview?->tanggal_review,
        ])->all();

        return [['ID Abstrak', 'Judul', 'Scope', 'Metode Presentasi', 'Status Seleksi', 'Tanggal Submit', 'Penulis', 'Email', 'Instansi',
            'Event', 'Hasil Review Terakhir', 'Catatan Review', 'Reviewer', 'Tanggal Review'], $rows];
    }

    public function invoiceRows(array $f): array
    {
        $q = $this->regFilter(Invoice::with(['registration.participant', 'registration.registrationPeriod']), $f, viaRegistration: true);
        $q->when(! empty($f['status']), fn ($x) => $x->where('status_pembayaran', $f['status']));

        $rows = $q->orderBy('id_invoice')->get()->map(fn (Invoice $i) => [
            $i->nomor_invoice, (string) $i->tanggal_invoice, optional($i->tanggal_jatuh_tempo)->toDateString(), optional($i->tanggal_jatuh_tempo_baru)->toDateString(),
            $i->registration?->participant?->full_name, $i->registration?->registration_type, $i->registration?->attendance_mode,
            $i->registration?->registrationPeriod?->nama_periode, (float) $i->total_tagihan, $i->status_pembayaran,
        ])->all();

        return [['Nomor Invoice', 'Tanggal Invoice', 'Jatuh Tempo', 'Jatuh Tempo Baru', 'Peserta', 'Kategori', 'Mode Kehadiran', 'Periode', 'Total Tagihan', 'Status'], $rows];
    }

    public function paymentRows(array $f): array
    {
        $q = $this->regFilter(Invoice::with(['registration.participant.userAccount', 'latestPaymentProof.verification.treasurer']), $f, viaRegistration: true);
        $q->when(! empty($f['status']), fn ($x) => $x->where('status_pembayaran', $f['status']));

        $rows = $q->orderBy('id_invoice')->get()->map(function (Invoice $i) {
            $proof = $i->latestPaymentProof;
            $ver = $proof?->verification;

            return [
                $i->nomor_invoice, $i->registration?->participant?->full_name, $i->registration?->participant?->userAccount?->email,
                $i->status_pembayaran, (float) $i->total_tagihan, $proof?->nominal_transfer !== null ? (float) $proof->nominal_transfer : null,
                (string) $proof?->tanggal_upload, $ver?->hasil_verifikasi, $ver?->alasan_penolakan, $ver?->treasurer?->email, (string) $ver?->tanggal_verifikasi,
            ];
        })->all();

        return [['Nomor Invoice', 'Peserta', 'Email', 'Status Pembayaran', 'Total Tagihan', 'Nominal Transfer', 'Tanggal Upload Bukti',
            'Hasil Verifikasi', 'Alasan Penolakan', 'Bendahara', 'Tanggal Verifikasi'], $rows];
    }

    /** Ringkasan rekap multi-sheet untuk ekspor. @return array<string, array{0: array<int,string>, 1: array<int, array<int,mixed>>}> */
    public function recapSheets(array $f): array
    {
        $reg = $this->registrations($f);
        $part = $this->participants($f);
        $abs = $this->abstracts($f);
        $inv = $this->invoices($f);
        $pay = $this->payments($f);

        return [
            'Registrasi' => [['Kelompok', 'Nilai', 'Jumlah'], array_merge(
                [['Total', '', $reg['total']]],
                ...collect(['by_status' => 'Status', 'by_registration_type' => 'Kategori', 'by_attendance_mode' => 'Mode', 'by_participation_type' => 'Partisipasi', 'by_publication_option' => 'Publikasi', 'by_period' => 'Periode'])
                    ->map(fn ($label, $key) => array_map(fn ($r) => [$label, $r['key'], $r['total']], $reg[$key]))->values()->all()
            )],
            'Peserta' => [['Periode/Negara', 'Kategori', 'Jumlah'], array_merge(
                array_map(fn ($r) => [$r['period'], $r['registration_type'], $r['total']], $part['by_category_and_period']),
                array_map(fn ($r) => [$r['key'], 'Negara', $r['total']], $part['by_country'])
            )],
            'Abstrak' => [['Kelompok', 'Nilai', 'Jumlah'], array_merge(
                [['Total', '', $abs['total']], ['Diterima', '', $abs['selection']['diterima']], ['Ditolak', '', $abs['selection']['ditolak']], ['Menunggu seleksi', '', $abs['selection']['menunggu_seleksi']]],
                array_map(fn ($r) => ['Metode', $r['key'], $r['total']], $abs['by_presentation_method']),
                array_map(fn ($r) => ['Scope', $r['key'], $r['total']], $abs['by_scope'])
            )],
            'Invoice' => [['Status', 'Jumlah', 'Total Tagihan'], array_map(fn ($r) => [$r['key'], $r['total'], (float) $r['total_tagihan']], $inv['by_status'])],
            'Pembayaran' => [['Indikator', 'Nilai'], [
                ['Bukti diunggah', $pay['proofs_total']], ['Valid', $pay['verified_valid']], ['Tidak valid', $pay['verified_invalid']],
                ['Menunggu verifikasi', $pay['awaiting_verification']], ['Nominal terverifikasi', (float) $pay['amount_verified']], ['Nominal belum lunas', (float) $pay['amount_outstanding']],
            ]],
        ];
    }
}
