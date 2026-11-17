<?php

namespace App\Console\Commands;

use App\Models\ContactPerson;
use App\Models\DocumentTemplate;
use App\Models\Event;
use App\Models\EventContent;
use App\Models\EventSchedule;
use App\Models\Faq;
use App\Models\ImportantDate;
use App\Models\RegistrationFee;
use App\Models\RegistrationPeriod;
use App\Models\Speaker;
use App\Models\SponsorshipPackage;
use App\Services\DocumentService;
use App\Support\DemoSamples;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Data contoh untuk landing (uji coba/UAT). Semua baris yang dibuat dicatat di app_setting 'demo_landing'
 * sehingga `--remove` hanya menghapus data contoh, tidak menyentuh data asli panitia.
 * Nama pembicara/panitia/kontak sengaja fiktif dan berlabel "Contoh"; tarif juga contoh.
 */
class DemoLandingContent extends Command
{
    protected $signature = 'asic:demo-landing {--remove : Hapus semua data contoh yang pernah dibuat perintah ini}';

    protected $description = 'Isi (atau hapus) data contoh landing: event, periode, tarif, pembicara, program, tanggal penting, konten, FAQ, kontak, sponsor';

    private const KEY = 'demo_landing';

    /** @var array<string, list<int>> */
    private array $created = [];

    public function handle(): int
    {
        return $this->option('remove') ? $this->remove() : $this->seed();
    }

    private function seed(): int
    {
        if ($row = DB::table('app_setting')->where('key', self::KEY)->first()) {
            // Data contoh lama: cukup lengkapi foto pembicara contoh yang belum punya foto.
            $this->created = json_decode((string) $row->value, true) ?: [];
            $added = $this->attachSpeakerPhotos($this->created['speaker'] ?? []);
            $this->relabel();
            $event = Event::whereKey($this->created['event'] ?? [])->first() ?? Event::query()->where('status_event', 'Aktif')->orderByDesc('tanggal_mulai')->first();
            $samples = [];
            if ($event) {
                $demo = new DemoSamples($this->created);
                $samples = $demo->seed($event);
                $this->created = $demo->created();
            }
            DB::table('app_setting')->where('key', self::KEY)->update(['value' => json_encode($this->created), 'updated_at' => now()]);
            $this->info('Data contoh diperbarui; foto pembicara: '.$added.'; contoh baru: '.(implode(', ', $samples) ?: '-').'.');

            return self::SUCCESS;
        }

        DB::transaction(function (): void {
            $event = Event::query()->where('status_event', 'Aktif')->orderByDesc('tanggal_mulai')->first();
            if (! $event) {
                $event = Event::create([
                    'nama_event' => '4th ASIC 2027',
                    'tema' => 'Integrating Smart Farming into Sustainable Agriculture: Innovation, Collaboration, and Resilience',
                    'deskripsi' => 'The 4th Agrifood System International Conference',
                    'tanggal_mulai' => '2027-06-09', 'tanggal_selesai' => '2027-06-10',
                    'lokasi' => 'Convention Hall, Universitas Andalas',
                    'mode_kegiatan' => 'Hybrid', 'status_event' => 'Aktif',
                ]);
                $this->track('event', $event->id_event);
            }
            $id = $event->id_event;

            if (! RegistrationFee::where('id_event', $id)->exists()) {
                $this->fees($id);
            }
            if (! Speaker::where('id_event', $id)->exists()) {
                $this->speakers($id);
            }
            if (! EventSchedule::where('id_event', $id)->exists()) {
                $this->program($id);
            }
            if (! ImportantDate::where('id_event', $id)->exists()) {
                $this->dates($id);
            }
            $this->content($id);
            $this->extras($id);

            DB::table('app_setting')->insert(['key' => self::KEY, 'value' => json_encode($this->created), 'created_at' => now(), 'updated_at' => now()]);
        });
        $event = Event::whereKey($this->created['event'] ?? [])->first() ?? Event::query()->where('status_event', 'Aktif')->orderByDesc('tanggal_mulai')->first();
        if ($event) {
            $demo = new DemoSamples($this->created);
            $demo->seed($event);
            $this->created = $demo->created();
            DB::table('app_setting')->where('key', self::KEY)->update(['value' => json_encode($this->created), 'updated_at' => now()]);
        }

        $this->info('Data contoh landing dibuat: '.collect($this->created)->map(fn ($ids, $t) => $t.' '.count($ids))->implode(', ').'.');

        return self::SUCCESS;
    }

    private function remove(): int
    {
        $row = DB::table('app_setting')->where('key', self::KEY)->first();
        if (! $row) {
            $this->info('Tidak ada data contoh yang tercatat.');

            return self::SUCCESS;
        }
        $created = json_decode((string) $row->value, true) ?: [];
        $models = [
            'fee' => RegistrationFee::class, 'period' => RegistrationPeriod::class, 'speaker' => Speaker::class, 'schedule' => EventSchedule::class,
            'date' => ImportantDate::class, 'content' => EventContent::class, 'faq' => Faq::class, 'contact' => ContactPerson::class,
            'package' => SponsorshipPackage::class, 'event' => Event::class,
        ];
        DB::transaction(fn () => DemoSamples::remove($created));
        $inUse = DB::table('registration')->whereIn('id_period', $created['period'] ?? [])->exists();
        DB::transaction(function () use ($created, $models, $inUse): void {
            foreach ($models as $key => $model) {
                $ids = $created[$key] ?? [];
                if ($key === 'fee' && $inUse) {
                    continue; // tarif dipakai invoice pendaftaran uji coba
                }
                if ($key === 'period' && $ids && $inUse) {
                    // Periode contoh sudah dipakai pendaftaran uji coba: nonaktifkan saja agar data pendaftar tidak hilang.
                    $model::whereKey($ids)->update(['status_periode' => 'Tidak Aktif']);

                    continue;
                }
                if ($key === 'event' && $ids && DB::table('registration')->whereIn('id_event', $ids)->exists()) {
                    $model::whereKey($ids)->update(['status_event' => 'Draft']);

                    continue;
                }
                if ($ids && $key === 'event') {
                    // Template nomor dokumen dibuat otomatis untuk event contoh; hapus bersama event.
                    DocumentTemplate::whereIn('id_event', $ids)->get()->each(function ($t) {
                        $t->file_template && Storage::disk(app(DocumentService::class)->disk())->delete($t->file_template);
                        $t->delete();
                    });
                }
                if ($ids) {
                    $model::whereKey($ids)->get()->each->delete();
                }
            }
            foreach ($created['file'] ?? [] as $file) {
                Storage::disk('public')->delete($file);
            }
            DB::table('app_setting')->where('key', self::KEY)->delete();
        });
        $this->info('Data contoh landing dihapus.');

        return self::SUCCESS;
    }

    /** Data contoh lama berlabel "(Contoh)" diganti label netral. */
    private function relabel(): void
    {
        Speaker::whereKey($this->created['speaker'] ?? [])->get()->each(fn (Speaker $s) => $s->update(['nama_speaker' => trim(str_replace('(Contoh)', '', $s->nama_speaker))]));
        EventContent::whereKey($this->created['content'] ?? [])->where('section', 'committee')->update(['isi_id' => 'Panitia 4th ASIC 2027', 'isi_en' => '4th ASIC 2027 Committee']);
        foreach (ContactPerson::whereKey($this->created['contact'] ?? [])->get() as $c) {
            $isTreasurer = str_contains($c->nama, 'Bendahara');
            $c->update(['nama' => $isTreasurer ? 'Bendahara 4th ASIC 2027' : 'Sekretariat 4th ASIC 2027', 'email' => $isTreasurer ? 'bendahara@asic.unand.ac.id' : 'sekretariat@asic.unand.ac.id']);
        }
        SponsorshipPackage::whereKey($this->created['package'] ?? [])->get()->each(fn ($p) => $p->update(['nama' => trim(str_replace('(Contoh)', '', $p->nama))]));
    }

    private function track(string $type, mixed $id): void
    {
        $this->created[$type][] = $id;
    }

    private function fees(int $event): void
    {
        $periods = [
            ['Early Bird', now()->toDateString(), '2027-03-31', 'Aktif', 1.0],
            ['Regular', '2027-04-01', '2027-05-15', 'Tidak Aktif', 1.25],
        ];
        // Tarif contoh: [IDR, USD] untuk Luring / Daring; publikasi menambah biaya.
        $base = ['Student' => ['Luring' => [750000, 75], 'Daring' => [500000, 50]], 'Regular' => ['Luring' => [1500000, 150], 'Daring' => [1000000, 100]]];
        $publication = ['Tidak' => [0, 0], 'Prosiding' => [1000000, 100], 'Jurnal' => [1500000, 150]];
        foreach ($periods as [$name, $start, $end, $status, $factor]) {
            $period = RegistrationPeriod::create(['id_event' => $event, 'nama_periode' => $name, 'tanggal_mulai' => $start, 'tanggal_selesai' => $end, 'status_periode' => $status]);
            $this->track('period', $period->id_period);
            foreach ($base as $type => $modes) {
                foreach ($modes as $mode => [$idr, $usd]) {
                    foreach ($publication as $option => [$pubIdr, $pubUsd]) {
                        foreach (['IDR' => $idr * $factor + $pubIdr, 'USD' => $usd * $factor + $pubUsd] as $currency => $amount) {
                            $fee = RegistrationFee::create([
                                'id_event' => $event, 'id_period' => $period->id_period, 'registration_type' => $type, 'attendance_mode' => $mode,
                                'tujuan_publikasi' => $option, 'amount' => $amount, 'discount' => 0, 'mata_uang' => $currency,
                            ]);
                            $this->track('fee', $fee->id_fee);
                        }
                    }
                }
            }
        }
    }

    private function speakers(int $event): void
    {
        $rows = [
            ['Keynote Speaker 1', 'Partner University', 'Cambodia', 'Soil health for climate-resilient smart farming'],
            ['Keynote Speaker 2', 'Partner University', 'Australia', 'Smart cultivation strategies for limited land systems'],
            ['Keynote Speaker 3', 'Partner Research Institute', 'Pakistan', 'Breeding for smart and sustainable agriculture'],
            ['Keynote Speaker 4', 'Partner University', 'Vietnam', 'Ecological innovations in smart pest management'],
            ['Keynote Speaker 5', 'Partner University', 'Philippines', 'Empowering farmers for smart farming adoption'],
            ['Keynote Speaker 6', 'Universitas Andalas', 'Indonesia', 'Local wisdom to enhance smart farming systems'],
        ];
        foreach ($rows as [$name, $institution, $country, $topic]) {
            $this->track('speaker', Speaker::create(['id_event' => $event, 'nama_speaker' => $name, 'institusi' => $institution, 'negara' => $country, 'deskripsi' => $topic])->id_speaker);
        }
        $this->attachSpeakerPhotos($this->created['speaker'] ?? []);
    }

    /**
     * Foto potret stok (Pexels, lihat resources/demo/speakers/CREDITS.md) untuk pembicara contoh yang belum berfoto.
     *
     * @param  list<int>  $speakerIds
     */
    private function attachSpeakerPhotos(array $speakerIds): int
    {
        $added = 0;
        foreach (array_values($speakerIds) as $i => $id) {
            $speaker = Speaker::find($id);
            $source = resource_path('demo/speakers/speaker-'.($i + 1).'.jpg');
            if (! $speaker || $speaker->foto || ! is_file($source)) {
                continue;
            }
            $path = 'speakers/demo-speaker-'.($i + 1).'.jpg';
            Storage::disk('public')->put($path, file_get_contents($source));
            $speaker->update(['foto' => $path]);
            $this->created['file'][] = $path;
            $added++;
        }

        return $added;
    }

    private function program(int $event): void
    {
        $rows = [
            ['2027-06-09', '07:30', '08:00', 'Registration & re-registration', 'Main lobby', 'Show your attendance QR at the desk.'],
            ['2027-06-09', '08:00', '09:30', 'Opening ceremony', 'Convention Hall', 'Welcome remarks and cultural performance.'],
            ['2027-06-09', '09:30', '12:00', 'Keynote session I', 'Convention Hall', 'Plenary talks (hybrid).'],
            ['2027-06-09', '12:00', '13:00', 'Lunch break', 'Convention Hall', null],
            ['2027-06-09', '13:00', '15:30', 'Parallel oral sessions', 'Parallel rooms', '7 minutes presentation + 3 minutes discussion.'],
            ['2027-06-09', '15:30', '16:30', 'Poster session', 'Poster area', 'Presenters stand by their posters.'],
            ['2027-06-10', '08:00', '10:30', 'Keynote session II', 'Convention Hall', 'Plenary talks (hybrid).'],
            ['2027-06-10', '10:30', '13:00', 'Parallel oral sessions', 'Parallel rooms', null],
            ['2027-06-10', '14:00', '15:30', 'Best presenter awards & closing', 'Convention Hall', null],
            ['2027-06-11', '08:00', '16:00', 'Field trip', 'West Sumatra', 'Optional, registered participants only.'],
        ];
        foreach ($rows as [$date, $start, $end, $name, $place, $desc]) {
            $this->track('schedule', EventSchedule::create(['id_event' => $event, 'tanggal' => $date, 'waktu_mulai' => $start, 'waktu_selesai' => $end, 'nama_kegiatan' => $name, 'lokasi' => $place, 'deskripsi' => $desc])->id_schedule);
        }
    }

    private function dates(int $event): void
    {
        $rows = [
            ['Pengiriman abstrak', 'Abstract submission', '2026-12-01', '2027-03-15'],
            ['Pengumuman seleksi abstrak', 'Abstract notification', '2027-03-16', '2027-03-23'],
            ['Batas pembayaran early bird', 'Early bird payment deadline', '2027-03-31', null],
            ['Pengiriman bahan presentasi', 'Presentation file submission', '2027-05-24', '2027-06-01'],
            ['Hari konferensi', 'Conference days', '2027-06-09', '2027-06-10'],
            ['Field trip', 'Field trip', '2027-06-11', null],
            ['Pengiriman makalah lengkap', 'Full paper submission', '2027-06-21', '2027-06-28'],
        ];
        foreach ($rows as $i => [$labelId, $labelEn, $start, $end]) {
            $this->track('date', ImportantDate::create(['id_event' => $event, 'label_id' => $labelId, 'label_en' => $labelEn, 'tanggal_mulai' => $start, 'tanggal_selesai' => $end, 'status' => 'Dipublikasikan', 'urutan' => $i])->id_date);
        }
    }

    private function content(int $event): void
    {
        $blocks = [
            ['about', 'Tentang ASIC', 'About ASIC',
                "ASIC diselenggarakan Fakultas Pertanian Universitas Andalas sejak 2018 (1st ASIC 2018, 2nd 2022, 3rd 2024). Edisi keempat mengangkat pertanian cerdas yang sesuai karakter pertanian tropis: teknologi digital, pemanfaatan lahan sempit, dan kearifan lokal.\nForum ini mempertemukan akademisi, peneliti, industri, pembuat kebijakan, dan masyarakat untuk memperkuat kolaborasi riset menuju sistem pangan yang tangguh.",
                "ASIC has been organised by the Faculty of Agriculture, Universitas Andalas since 2018 (1st ASIC 2018, 2nd 2022, 3rd 2024). The fourth edition focuses on smart farming suited to tropical agriculture: digital technology, efficient use of limited land, and local wisdom.\nThe forum brings together academics, researchers, industry, policymakers and communities to strengthen research collaboration towards resilient food systems.", null],
            ['scope', 'Crop Production', null, 'Budidaya, fisiologi, dan teknologi produksi tanaman.', 'Cultivation, physiology and crop production technology.', null],
            ['scope', 'Plant Breeding', null, 'Pemuliaan, genetika, dan bioteknologi tanaman.', 'Plant breeding, genetics and biotechnology.', null],
            ['scope', 'Plant Protection and Food Safety', null, 'Hama, penyakit, pengendalian hayati, dan keamanan pangan.', 'Pests, diseases, biological control and food safety.', null],
            ['scope', 'Soil Management', null, 'Kesuburan tanah, konservasi, dan pengelolaan lahan.', 'Soil fertility, conservation and land management.', null],
            ['scope', 'Socio-Economic of Agriculture', null, 'Ekonomi, kebijakan, penyuluhan, dan kelembagaan pertanian.', 'Agricultural economics, policy, extension and institutions.', null],
            ['scope', 'Other Topics related to Agriculture', null, 'Topik pertanian lain, termasuk pertanian cerdas dan pangan.', 'Other agricultural topics, including smart farming and food.', null],
            ['presentation_oral', 'Presentasi oral', 'Oral presentation', 'Siapkan PPT: 7 menit presentasi dan 3 menit diskusi.', 'Prepare slides: 7 minutes presentation and 3 minutes discussion.', null],
            ['presentation_poster', 'Presentasi poster', 'Poster presentation', 'X-banner 60 × 160 cm berisi judul, penulis, afiliasi, abstrak, pendahuluan, metode, hasil, dan kesimpulan. Presenter berdiri di dekat poster saat penilaian.', 'X-banner 60 × 160 cm with title, authors, affiliation, abstract, introduction, methods, results and conclusion. Presenters stand by their poster during judging.', null],
            ['publication', 'IOP Conference Series: Earth and Environmental Science', null, 'Prosiding terindeks Scopus (rencana).', 'Scopus-indexed proceedings (planned).', null],
            ['publication', 'International Journal of Agriculture Science (IJASC)', null, 'DOAJ, Sinta 2 (rencana).', 'DOAJ, Sinta 2 (planned).', null],
            ['publication', 'Jurnal Proteksi Tanaman (JPT)', null, 'DOAJ, Sinta 3 (rencana).', 'DOAJ, Sinta 3 (planned).', null],
            ['publication', 'JERAMI Indonesian Journal of Crop Science', null, 'DOAJ, Sinta 3 (rencana).', 'DOAJ, Sinta 3 (planned).', null],
            ['facilities', 'Peserta luring', 'Onsite participants', 'Sertifikat, seminar kit, materi narasumber, conference book, 1× makan siang, 2× coffee break, akses sesi pleno dan paralel.', 'Certificate, seminar kit, speaker materials, conference book, 1 lunch, 2 coffee breaks, access to plenary and parallel sessions.', null],
            ['facilities', 'Peserta daring', 'Online participants', 'E-sertifikat, e-conference book, akses hybrid sesi pleno dan paralel.', 'E-certificate, e-conference book, hybrid access to plenary and parallel sessions.', null],
            ['venue_info', 'Akses lokasi', 'Getting there', 'Kampus Limau Manis, Padang, sekitar 45–60 menit berkendara dari Bandara Internasional Minangkabau.', 'Limau Manis campus, Padang, about 45–60 minutes by car from Minangkabau International Airport.', null],
        ];
        foreach (['Ketua Panitia', 'Sekretaris', 'Bendahara', 'Bidang Kesekretariatan', 'Bidang Abstrak & Publikasi', 'Bidang Acara', 'Bidang Field Trip', 'Bidang Konsumsi'] as $role) {
            $blocks[] = ['committee', $role, null, 'Panitia 4th ASIC 2027', '4th ASIC 2027 Committee', null];
        }
        foreach ($blocks as $i => [$section, $titleId, $titleEn, $bodyId, $bodyEn, $link]) {
            $this->track('content', EventContent::create([
                'id_event' => $event, 'section' => $section, 'judul_id' => $titleId, 'judul_en' => $titleEn ?? $titleId,
                'isi_id' => $bodyId, 'isi_en' => $bodyEn, 'tautan' => $link, 'status' => 'Dipublikasikan', 'urutan' => $i,
            ])->id_content);
        }
    }

    private function extras(int $event): void
    {
        $faqs = [
            ['Apakah boleh hadir secara daring?', 'Can I attend online?', 'Boleh. Konferensi berlangsung hybrid; pilih mode daring saat registrasi.', 'Yes. The conference is hybrid; choose online mode when registering.'],
            ['Berapa batas waktu pembayaran?', 'What is the payment deadline?', 'Satu minggu setelah LoA dan invoice terbit. Harga early bird hangus bila lewat.', 'One week after the LoA and invoice are issued. Early bird rates lapse after that.'],
            ['Apakah Listener mendapat LoA?', 'Do listeners receive an LoA?', 'Ya, LoA Listener terbit otomatis setelah registrasi selesai.', 'Yes, the listener LoA is issued automatically after registration.'],
            ['Bagaimana syarat sertifikat?', 'What are the certificate requirements?', 'Presenter wajib presentasi sesuai jadwal dan mengisi absensi; Listener cukup mengisi absensi.', 'Presenters must present as scheduled and record attendance; listeners only need to record attendance.'],
        ];
        foreach ($faqs as $i => [$qId, $qEn, $aId, $aEn]) {
            $this->track('faq', Faq::create(['id_event' => $event, 'pertanyaan_id' => $qId, 'pertanyaan_en' => $qEn, 'jawaban_id' => $aId, 'jawaban_en' => $aEn, 'kategori' => 'Umum', 'status' => 'Dipublikasikan', 'urutan' => $i])->id_faq);
        }
        foreach ([['Sekretariat 4th ASIC 2027', 'Sekretariat', 'Secretariat', 'sekretariat@asic.unand.ac.id'], ['Bendahara 4th ASIC 2027', 'Pembayaran', 'Payments', 'bendahara@asic.unand.ac.id']] as $i => [$name, $roleId, $roleEn, $email]) {
            $this->track('contact', ContactPerson::create(['id_event' => $event, 'nama' => $name, 'peran_id' => $roleId, 'peran_en' => $roleEn, 'email' => $email, 'status' => 'Dipublikasikan', 'urutan' => $i])->id_contact);
        }
        $packages = [
            ['Platinum', 'Logo utama di website, backdrop, dan conference book; booth pameran.', 'Main logo on the website, backdrop and conference book; exhibition booth.'],
            ['Gold', 'Logo di website dan conference book; booth pameran.', 'Logo on the website and conference book; exhibition booth.'],
            ['Silver', 'Logo di website dan conference book.', 'Logo on the website and conference book.'],
        ];
        foreach ($packages as $i => [$name, $benefitId, $benefitEn]) {
            $this->track('package', SponsorshipPackage::create(['id_event' => $event, 'nama' => $name, 'manfaat_id' => $benefitId, 'manfaat_en' => $benefitEn, 'harga_teks' => 'Hubungi panitia / Contact the committee', 'status' => 'Dipublikasikan', 'urutan' => $i])->getKey());
        }
    }
}
