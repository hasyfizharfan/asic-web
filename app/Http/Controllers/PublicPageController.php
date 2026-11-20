<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use App\Services\PublicContent;
use Illuminate\View\View;

/**
 * Halaman publik penuh (server-rendered, dua bahasa). Isi hanya dari konten terpublikasi;
 * bila kosong, halaman menyatakan informasinya belum dipublikasikan (bukan teks karangan).
 */
class PublicPageController extends Controller
{
    public const PAGES = ['venue', 'guidelines', 'faq', 'contact', 'sponsorship', 'travel', 'gallery', 'terms', 'privacy', 'ethics'];

    public function __construct(private readonly PublicContent $content) {}

    public function show(string $page): View
    {
        abort_unless(in_array($page, self::PAGES, true), 404);
        $event = $this->content->activeEvent();

        $data = ['page' => $page, 'event' => $event, 'blocks' => collect(), 'items' => collect(), 'legal' => null, 'packages' => collect(), 'sponsors' => collect(), 'contacts' => collect(), 'channels' => collect()];
        match ($page) {
            'venue' => $data['blocks'] = $this->content->blocks($event, ['accommodation', 'pickup']),
            'guidelines' => $data['blocks'] = $this->content->blocks($event, ['presentation_oral', 'presentation_poster']),
            'faq' => $data['items'] = $this->content->faqs($event),
            'contact' => [$data['contacts'], $data['channels']] = [$this->content->contacts($event), $this->content->channels($event)],
            'sponsorship' => [$data['blocks'], $data['packages'], $data['sponsors']] = [$this->content->blocks($event, ['sponsorship_intro', 'exhibition_intro']), $this->content->packages($event), $this->content->sponsors($event)],
            'travel' => [$data['blocks'], $data['contacts']] = [$this->content->blocks($event, ['visa', 'travel']), $this->content->contacts($event)],
            'gallery' => $data['items'] = $this->content->photos($event),
            'terms', 'privacy', 'ethics' => $data['legal'] = LegalDocument::where('jenis', $page)->where('status', 'Dipublikasikan')->first(),
        };

        return view('public.page', $data);
    }
}
