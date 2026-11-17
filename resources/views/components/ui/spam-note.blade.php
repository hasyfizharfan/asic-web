{{-- Pengingat cek folder Spam untuk email dari sistem. Alamat pengirim mengikuti MAIL_FROM_ADDRESS. --}}
@props(['compact' => false])
@if (config('asic.notify_email', true))
    @if ($compact)
        <span {{ $attributes->merge(['class' => 'text-[11px] leading-snug text-on-surface-variant']) }} ><span class="material-symbols-outlined text-[16px] text-[#7a5d00]" aria-hidden="true">mark_email_unread</span>{{ __('asic-common.email_spam_short') }}</span>
    @else
        <div {{ $attributes->merge(['class' => 'flex items-start gap-2 rounded-xl border border-[#F5C518]/50 bg-[#FEF6D8] p-3 text-xs leading-relaxed text-[#5c4600]']) }}>
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">mark_email_unread</span>
            <span>{{ __('asic-common.email_spam_note', ['from' => config('mail.from.address')]) }}</span>
        </div>
    @endif
@endif
