<?php

namespace App\Support\Content;

use App\Models\ContactPerson;
use App\Models\EventContent;
use App\Models\Faq;
use App\Models\ImportantDate;
use App\Models\SponsorshipPackage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

/**
 * Registri jenis konten publik sederhana (CRUD seragam). Foto, permintaan sponsor, dan dokumen
 * hukum memiliki controller sendiri karena aturan khususnya (unggah berkas, status, versi).
 */
final class ContentTypes
{
    public const PUBLISH_STATUSES = ['Draft', 'Dipublikasikan'];

    /** Tautan hanya https:// atau mailto: (tanpa javascript:, data:, http:). */
    public const LINK_RULE = 'regex:/^(https:\/\/|mailto:)[^\s<>"]+$/i';

    /** @return array<string, array{model: class-string<Model>, key: string, event_required: bool, order: list<string>}> */
    public static function all(): array
    {
        return [
            'event-content' => ['model' => EventContent::class, 'key' => 'id_content', 'event_required' => true, 'order' => ['section', 'urutan', 'id_content']],
            'important-dates' => ['model' => ImportantDate::class, 'key' => 'id_date', 'event_required' => true, 'order' => ['tanggal_mulai', 'urutan', 'id_date']],
            'faqs' => ['model' => Faq::class, 'key' => 'id_faq', 'event_required' => false, 'order' => ['urutan', 'id_faq']],
            'contacts' => ['model' => ContactPerson::class, 'key' => 'id_contact', 'event_required' => false, 'order' => ['urutan', 'id_contact']],
            'sponsorship-packages' => ['model' => SponsorshipPackage::class, 'key' => 'id_package', 'event_required' => true, 'order' => ['urutan', 'id_package']],
        ];
    }

    /** @return array{model: class-string<Model>, key: string, event_required: bool, order: list<string>} */
    public static function get(string $type): array
    {
        return self::all()[$type] ?? throw new InvalidArgumentException("Jenis konten tidak dikenal: {$type}");
    }

    /** @return array<string, array<int, mixed>> */
    public static function rules(string $type, bool $partial): array
    {
        $p = $partial ? ['sometimes'] : [];
        $req = $partial ? ['sometimes', 'required'] : ['required'];
        $event = self::get($type)['event_required'] ? [...$req, 'integer', 'exists:event,id_event'] : [...$p, 'nullable', 'integer', 'exists:event,id_event'];
        $common = [
            'id_event' => $event,
            'status' => [...$p, Rule::in(self::PUBLISH_STATUSES)],
            'urutan' => [...$p, 'integer', 'min:0', 'max:65535'],
        ];
        $text = fn (int $max) => [...$p, 'nullable', 'string', 'max:'.$max];
        $long = [...$p, 'nullable', 'string', 'max:20000'];

        return $common + match ($type) {
            'event-content' => [
                'section' => [...$req, Rule::in(EventContent::SECTIONS)],
                'judul_id' => $text(200), 'judul_en' => $text(200), 'isi_id' => $long, 'isi_en' => $long,
                'tautan' => [...$p, 'nullable', 'string', 'max:500', self::LINK_RULE],
            ],
            'important-dates' => [
                'label_id' => $text(200), 'label_en' => $text(200),
                'tanggal_mulai' => [...$req, 'date'],
                'tanggal_selesai' => [...$p, 'nullable', 'date', 'after_or_equal:tanggal_mulai'],
            ],
            'faqs' => [
                'pertanyaan_id' => $text(300), 'pertanyaan_en' => $text(300), 'jawaban_id' => $long, 'jawaban_en' => $long,
                'kategori' => $text(60),
            ],
            'contacts' => [
                'nama' => [...$req, 'string', 'max:150'],
                'peran_id' => $text(150), 'peran_en' => $text(150),
                'email' => [...$p, 'nullable', 'email', 'max:150'],
                'telepon' => [...$p, 'nullable', 'string', 'max:30', 'regex:/^[0-9+()\-\s]+$/'],
                'whatsapp' => [...$p, 'nullable', 'string', 'max:30', 'regex:/^[0-9+()\-\s]+$/'],
            ],
            'sponsorship-packages' => [
                'nama' => [...$req, 'string', 'max:150'],
                'deskripsi_id' => $long, 'deskripsi_en' => $long, 'manfaat_id' => $long, 'manfaat_en' => $long,
                'harga_teks' => $text(150),
                'kuota' => [...$p, 'nullable', 'integer', 'min:0', 'max:100000'],
            ],
            default => [],
        };
    }

    /** Pasangan dua bahasa yang wajib terisi minimal satu bahasa per jenis. @return list<string> */
    public static function requiredBilingual(string $type): array
    {
        return match ($type) {
            'event-content' => ['judul', 'isi'],
            'important-dates' => ['label'],
            'faqs' => ['pertanyaan', 'jawaban'],
            default => [],
        };
    }
}
