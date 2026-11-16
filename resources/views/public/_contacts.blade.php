{{-- Kontak PIC: hanya kolom yang diisi panitia. --}}
<div class="grid gap-4 md:grid-cols-2">
    @foreach($contacts as $contact)
        <article class="rounded-2xl border border-[#d9e3d0] bg-white p-5 text-sm">
            <p class="font-bold text-[#0B5D2E]">{{ $contact->nama }}</p>
            @if($contact->localized('peran'))<p class="text-xs text-[#3b4a40]">{{ $contact->localized('peran') }}</p>@endif
            <ul class="mt-2 space-y-1">
                @if($contact->email)<li><a class="text-[#0B5D2E] underline" href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></li>@endif
                @if($contact->telepon)<li>{{ $contact->telepon }}</li>@endif
                @if($contact->whatsapp)<li><a class="text-[#0B5D2E] underline" href="https://wa.me/{{ preg_replace('/\D/', '', $contact->whatsapp) }}" target="_blank" rel="noopener noreferrer">WhatsApp {{ $contact->whatsapp }}</a></li>@endif
            </ul>
        </article>
    @endforeach
</div>
