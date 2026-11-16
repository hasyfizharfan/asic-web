@php($t = trans('asic-documents.archive'))
@extends('layouts.portal', ['title' => __('asic-documents.archive.title'), 'role' => 'admin', 'roleName' => __('asic-documents.archive.title'), 'nav' => $adminNav])

@section('content')
<main class="space-y-5" x-data="{
    i18n: @js($t), busy: false, error: '',
    async archive(id) {
        if (!window.confirm(this.i18n.confirm_archive)) return;
        this.busy = true; this.error = '';
        const response = await window.asicApi.patch('/api/content/events/' + id, { status_event: 'Arsip' });
        this.busy = false;
        if (!response.ok) { this.error = response.error || this.i18n.load_failed; return; }
        window.location.reload();
    }
}">
    <header><h1 class="text-2xl font-bold">{{ $t['title'] }}</h1><p class="mt-1 text-sm text-on-surface-variant">{{ $t['admin_subtitle'] }}</p></header>
    <p x-show="error" x-text="error" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p>
    @if($editions->isEmpty())
        <p class="rounded-xl border p-6 text-center text-sm text-on-surface-variant">{{ $t['empty'] }}</p>
    @else
        <div class="overflow-x-auto rounded-xl border border-surface-container-high bg-surface-container-lowest">
            <table class="w-full text-left text-sm">
                <thead><tr class="border-b bg-surface-container-low text-xs uppercase"><th class="p-3">{{ $t['col_event'] }}</th><th class="p-3">{{ $t['col_status'] }}</th><th class="p-3">{{ $t['col_registrations'] }}</th><th class="p-3">{{ $t['col_presenters'] }}</th><th class="p-3">{{ $t['col_accepted'] }}</th><th class="p-3">{{ $t['col_attendance'] }}</th><th class="p-3"></th></tr></thead>
                <tbody>
                    @foreach($editions as $row)
                        <tr class="border-b border-surface-container-highest">
                            <td class="p-3"><p class="font-semibold">{{ $row['event']->nama_event }}</p><p class="text-xs text-on-surface-variant">{{ $row['event']->tanggal_mulai }}</p></td>
                            <td class="p-3">{{ $row['event']->status_event }}</td>
                            <td class="p-3">{{ $row['stats']['registrations'] }}</td>
                            <td class="p-3">{{ $row['stats']['presenters'] }}</td>
                            <td class="p-3">{{ $row['stats']['accepted_abstracts'] }}</td>
                            <td class="p-3">{{ $row['stats']['attendees'] }}</td>
                            <td class="p-3">@if($row['event']->status_event === 'Selesai')<button type="button" @click="archive({{ (int) $row['event']->id_event }})" :disabled="busy" class="rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white disabled:opacity-40">{{ $t['archive_action'] }}</button>@endif</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    <section class="grid gap-3 md:grid-cols-2 text-sm text-on-surface-variant">
        <p class="rounded-xl bg-surface-container-low p-4">{{ $t['proceedings_unavailable'] }}</p>
        <p class="rounded-xl bg-surface-container-low p-4">{{ $t['faculty_unavailable'] }}</p>
    </section>
</main>
@endsection
