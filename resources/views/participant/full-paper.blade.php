@extends('layouts.portal', [
    'role' => 'participant'
])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <x-layout.page-header title="{{ __('asic-participant.full_paper_title') }}" subtitle="{{ __('asic-participant.full_paper_desc') }}" :breadcrumbs="[
        ['label' => 'Portal', 'url' => route('fe.participant.dashboard')],
        ['label' => 'Full Paper & Files']
    ]">
        <x-ui.badge variant="amber" dot>Due: 15 July 2027</x-ui.badge>
    </x-layout.page-header>

    <!-- Paper Context Banner -->
    <x-ui.card>
        <div class="flex items-start justify-between gap-4">
            <div>
                <span class="font-mono text-xs font-bold text-primary bg-primary-fixed/30 px-2 py-0.5 rounded">
                    ASIC-2027-ABS-0089
                </span>
                <h3 class="text-base font-bold text-on-surface font-display mt-2">
                    Application of Biochar and Arbuscular Mycorrhizal Fungi in Restoring Acid Tropical Agricultural Soils
                </h3>
                <p class="text-xs text-on-surface-variant mt-1">Topic: Sustainable Food Production & Soil Health</p>
            </div>
            <x-ui.button size="sm" variant="outline" href="{{ route('fe.participant.abstract') }}" iconLeft="visibility">
                View Abstract
            </x-ui.button>
        </div>
    </x-ui.card>

    <!-- Upload Form & Template Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Upload Card -->
        <div class="lg:col-span-2 space-y-6">
            {{-- TODO(BE): action route('fe.participant.full-paper.store') --}}
            <form action="{{ route('fe.participant.full-paper') }}" method="GET" class="space-y-4" @submit.prevent="$store.toast.add('Manuscript version successfully uploaded!', 'success')">
                @csrf
                <x-ui.card title="Upload Camera-Ready Manuscript" icon="cloud_upload">
                    <div class="space-y-4">
                        <x-ui.input label="Manuscript Final Title" value="Application of Biochar and Arbuscular Mycorrhizal Fungi in Restoring Acid Tropical Agricultural Soils" required />
                        
                        <x-ui.file-upload label="Select Manuscript File (.DOCX / .PDF)" name="manuscript_file" accept=".docx,.pdf" maxSize="15 MB" hint="Must strictly follow ASIC 2027 author template formatting" required />

                        <x-ui.textarea label="Revisions & Author Notes" name="author_notes" placeholder="Summarize changes made addressing peer reviewer feedback..." rows="3" />
                    </div>

                    <x-slot:footer>
                        <div class="w-full flex items-center justify-between text-xs">
                            <span class="text-on-surface-variant">Ensure plagiarism check &lt; 20%</span>
                            <x-ui.button type="submit" variant="primary" iconLeft="upload">
                                Submit Manuscript
                            </x-ui.button>
                        </div>
                    </x-slot:footer>
                </x-ui.card>
            </form>

            <!-- Submission History Table -->
            <div>
                <h3 class="text-sm font-bold text-on-surface mb-3 font-display">Submission Version Log</h3>
                <x-ui.table :headers="['Version', 'File Name', 'Uploaded At', 'Similarity', 'Status']">
                    <tr>
                        <td class="px-5 py-3 font-mono font-bold text-primary">v1.2 (Current)</td>
                        <td class="px-5 py-3 font-medium">ASIC-0089_FullPaper_Rev1.docx</td>
                        <td class="px-5 py-3 font-mono text-[11px] text-on-surface-variant">2027-05-10</td>
                        <td class="px-5 py-3 font-mono text-tertiary font-bold">12% (Pass)</td>
                        <td class="px-5 py-3">
                            <x-ui.badge variant="success" dot>Approved</x-ui.badge>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-5 py-3 font-mono text-on-surface-variant">v1.0</td>
                        <td class="px-5 py-3 text-on-surface-variant">ASIC-0089_Draft.docx</td>
                        <td class="px-5 py-3 font-mono text-[11px] text-on-surface-variant">2027-04-14</td>
                        <td class="px-5 py-3 font-mono text-on-surface-variant">14%</td>
                        <td class="px-5 py-3">
                            <x-ui.badge variant="neutral">Archived</x-ui.badge>
                        </td>
                    </tr>
                </x-ui.table>
            </div>
        </div>

        <!-- Right: Author Guidelines & Downloads -->
        <div class="space-y-6">
            <x-ui.card title="Author Resources" icon="download">
                <div class="space-y-3">
                    <div class="p-3 rounded-xl bg-surface-container-low border border-surface-container-highest space-y-1">
                        <p class="font-bold text-xs text-on-surface">Official Paper Template</p>
                        <p class="text-[11px] text-on-surface-variant">Two-column IEEE / IOP standard layout with predefined styles.</p>
                        <div class="pt-2">
                            <x-ui.button size="sm" variant="outline" iconLeft="download" @click="$store.toast.add('Downloading ASIC Template .DOCX...', 'info')">
                                Download Template
                            </x-ui.button>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-surface-container-low border border-surface-container-highest space-y-1">
                        <p class="font-bold text-xs text-on-surface">Copyright Transfer Form</p>
                        <p class="text-[11px] text-on-surface-variant">Required for proceeding publisher agreement.</p>
                        <div class="pt-2">
                            <x-ui.button size="sm" variant="outline" iconLeft="download" @click="$store.toast.add('Downloading Copyright Form...', 'info')">
                                Download Form
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</div>
@endsection

