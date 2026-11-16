{{-- Form permintaan sponsor/exhibition: pesan sukses hanya setelah respons 201; tanpa localStorage; honeypot "website". --}}
<section class="rounded-2xl border border-[#d9e3d0] bg-white p-6" x-data="{
    i18n: @js(trans('asic-public.form')),
    form: { id_event: {{ (int) $event->id_event }}, id_package: '', jenis: 'Sponsorship', nama_organisasi: '', nama_kontak: '', email: '', telepon: '', pesan: '', website: '' },
    errors: {}, error: '', success: '', sending: false,
    async submit() {
        this.sending = true; this.errors = {}; this.error = ''; this.success = '';
        const payload = { ...this.form, id_package: this.form.id_package ? Number(this.form.id_package) : null };
        if (!payload.website) delete payload.website;
        const response = await window.asicApi.post('/api/public/sponsorship-inquiries', payload);
        this.sending = false;
        if (response.status !== 201) { this.errors = response.errors || {}; this.error = response.error || this.i18n.failed; return; }
        this.success = response.data?.message || '';
        this.form = { ...this.form, id_package: '', nama_organisasi: '', nama_kontak: '', email: '', telepon: '', pesan: '' };
    }
}">
    <h2 class="text-xl font-bold text-[#0B5D2E]">{{ __('asic-public.form.title') }}</h2>
    <p x-show="success" x-text="success" role="status" class="mt-3 rounded-xl bg-[#d7f5dd] p-3 text-sm text-[#0B5D2E]"></p>
    <p x-show="error" x-text="error" role="alert" class="mt-3 rounded-xl bg-[#fde2e1] p-3 text-sm text-[#7a1c17]"></p>
    <form class="mt-4 grid gap-3 sm:grid-cols-2" @submit.prevent="submit()">
        <label class="text-sm font-semibold">{{ __('asic-public.form.type') }}<select x-model="form.jenis" class="mt-1 block w-full rounded-xl border-[#c9d6c2]"><option value="Sponsorship">Sponsorship</option><option value="Exhibition">Exhibition</option></select></label>
        <label class="text-sm font-semibold">{{ __('asic-public.form.package') }}<select x-model="form.id_package" class="mt-1 block w-full rounded-xl border-[#c9d6c2]"><option value="">{{ __('asic-public.form.no_package') }}</option>@foreach($packages as $package)<option value="{{ $package->id_package }}">{{ $package->nama }}</option>@endforeach</select></label>
        <label class="text-sm font-semibold">{{ __('asic-public.form.organization') }}<input x-model="form.nama_organisasi" required maxlength="150" class="mt-1 block w-full rounded-xl border-[#c9d6c2]"><span x-show="errors.nama_organisasi" x-text="errors.nama_organisasi?.[0]" class="text-xs text-[#b3261e]"></span></label>
        <label class="text-sm font-semibold">{{ __('asic-public.form.contact') }}<input x-model="form.nama_kontak" required maxlength="150" class="mt-1 block w-full rounded-xl border-[#c9d6c2]"><span x-show="errors.nama_kontak" x-text="errors.nama_kontak?.[0]" class="text-xs text-[#b3261e]"></span></label>
        <label class="text-sm font-semibold">{{ __('asic-public.form.email') }}<input x-model="form.email" type="email" required maxlength="150" class="mt-1 block w-full rounded-xl border-[#c9d6c2]"><span x-show="errors.email" x-text="errors.email?.[0]" class="text-xs text-[#b3261e]"></span></label>
        <label class="text-sm font-semibold">{{ __('asic-public.form.phone') }}<input x-model="form.telepon" maxlength="30" class="mt-1 block w-full rounded-xl border-[#c9d6c2]"><span x-show="errors.telepon" x-text="errors.telepon?.[0]" class="text-xs text-[#b3261e]"></span></label>
        <label class="text-sm font-semibold sm:col-span-2">{{ __('asic-public.form.message') }}<textarea x-model="form.pesan" required minlength="10" maxlength="3000" rows="4" class="mt-1 block w-full rounded-xl border-[#c9d6c2]"></textarea><span x-show="errors.pesan" x-text="errors.pesan?.[0]" class="text-xs text-[#b3261e]"></span></label>
        <label class="absolute -left-[9999px]" aria-hidden="true">{{ __('asic-public.form.website') }}<input x-model="form.website" tabindex="-1" autocomplete="off"></label>
        <div class="sm:col-span-2"><button :disabled="sending" class="rounded-xl bg-[#0B5D2E] px-5 py-2.5 text-sm font-bold text-white disabled:opacity-50" x-text="sending ? i18n.sending : i18n.submit"></button></div>
    </form>
</section>
