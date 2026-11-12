@extends('layouts.portal', [
    'role' => 'admin',
    'roleName' => 'Developer Mode',
    'nav' => [
        ['label' => 'Component Gallery', 'route' => 'dev.ui', 'icon' => 'widgets'],
        ['label' => 'Public Landing', 'route' => 'home', 'icon' => 'public'],
        ['label' => 'Participant Login', 'route' => 'participant.login', 'icon' => 'person'],
        ['label' => 'Secretariat Login', 'route' => 'secretariat.login', 'icon' => 'badge'],
        ['label' => 'Treasurer Login', 'route' => 'treasurer.login', 'icon' => 'account_balance'],
        ['label' => 'Admin Login', 'route' => 'admin.login', 'icon' => 'admin_panel_settings']
    ]
])

@section('content')
<div class="space-y-10">
    <x-layout.page-header title="ASIC 2027 — UI Component Gallery" subtitle="Internal design system verification (Fase 1: Tokens, Layouts, & Blade Anonymous Components)">
        <x-ui.button variant="secondary" iconLeft="science" @click="$store.toast.add('Test Toast from Gallery!', 'success')">
            Trigger Toast
        </x-ui.button>
        <x-ui.button variant="primary" iconLeft="open_in_new" href="{{ route('fe.home') }}">
            View Landing
        </x-ui.button>
    </x-layout.page-header>

    <!-- 1. Typography & Client Font (Calibri) -->
    <x-ui.card title="1. Typography Scales (Font: Calibri / Client Directive)" icon="text_fields">
        <div class="space-y-4">
            <div class="p-4 rounded-xl bg-surface-container-low border border-surface-container-highest">
                <p class="text-xs uppercase tracking-wider text-primary font-bold mb-2">Display & Headlines</p>
                <div class="space-y-2">
                    <p class="text-display-lg">Display LG (36px / Calibri Bold) — Agrifood System 2027</p>
                    <p class="text-display-md">Display MD (30px / Calibri Bold) — Sustainable Agriculture</p>
                    <p class="text-headline-lg">Headline LG (24px / Calibri Bold) — International Conference</p>
                    <p class="text-headline-md">Headline MD (20px / Calibri Bold) — Faculty of Agriculture</p>
                    <p class="text-headline-sm">Headline SM (16px / Calibri Bold) — Universitas Andalas</p>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-surface-container-low border border-surface-container-highest">
                <p class="text-xs uppercase tracking-wider text-primary font-bold mb-2">Body & Labels</p>
                <div class="space-y-2">
                    <p class="text-body-lg">Body LG (16px) — The conference brings together scientists, practitioners, and leaders.</p>
                    <p class="text-body-md">Body MD (14px) — Regular interface body text used across tables, forms, and descriptions.</p>
                    <p class="text-body-sm">Body SM (12px) — Auxiliary captions, metadata, and helper instructions.</p>
                    <p class="text-label-lg">Label LG (14px Bold) — Form Field Label</p>
                    <p class="text-label-md">Label MD (12px Semi-Bold) — Table Header & Badge Text</p>
                    <p class="text-label-sm">Label SM (11px Bold) — Micro labels & status tags</p>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-surface-container-low border border-surface-container-highest">
                <p class="text-xs uppercase tracking-wider text-primary font-bold mb-2">Monospace / Code</p>
                <div class="space-y-2">
                    <p class="text-code-md">Code MD (13px JetBrains Mono): ASIC-2027-ABS-0089 #PAY-INV-2027-014</p>
                    <p class="text-code-sm">Code SM (12px JetBrains Mono): UUID: 89904493-9285-40d8-beda-d3dadbb9cb2d</p>
                </div>
            </div>
        </div>
    </x-ui.card>

    <!-- 2. Buttons -->
    <x-ui.card title="2. Interactive Buttons (Variants, Sizes, Icons)" icon="smart_button">
        <div class="space-y-6">
            <div>
                <p class="text-xs font-bold text-on-surface-variant uppercase mb-3">Variants</p>
                <div class="flex flex-wrap items-center gap-3">
                    <x-ui.button variant="primary" iconLeft="check">Primary Action</x-ui.button>
                    <x-ui.button variant="secondary" iconLeft="edit">Secondary</x-ui.button>
                    <x-ui.button variant="gold" iconLeft="star">Gold CTA</x-ui.button>
                    <x-ui.button variant="outline" iconLeft="download">Outline</x-ui.button>
                    <x-ui.button variant="ghost" iconLeft="visibility">Ghost</x-ui.button>
                    <x-ui.button variant="danger" iconLeft="delete">Danger Action</x-ui.button>
                    <x-ui.button variant="primary" disabled iconLeft="lock">Disabled</x-ui.button>
                </div>
            </div>

            <div>
                <p class="text-xs font-bold text-on-surface-variant uppercase mb-3">Sizes</p>
                <div class="flex flex-wrap items-center gap-3">
                    <x-ui.button size="sm" variant="primary" iconLeft="add">Small (sm)</x-ui.button>
                    <x-ui.button size="md" variant="primary" iconLeft="send">Medium (md)</x-ui.button>
                    <x-ui.button size="lg" variant="primary" iconLeft="upload_file">Large (lg)</x-ui.button>
                </div>
            </div>
        </div>
    </x-ui.card>

    <!-- 3. Badges -->
    <x-ui.card title="3. Badges & Status Indicators" icon="verified">
        <div class="flex flex-wrap items-center gap-3">
            <x-ui.badge variant="success" dot icon="check_circle">Verified / Accepted</x-ui.badge>
            <x-ui.badge variant="warning" dot icon="hourglass_empty">Waiting Payment</x-ui.badge>
            <x-ui.badge variant="amber" dot icon="pending">Under Review</x-ui.badge>
            <x-ui.badge variant="danger" dot icon="cancel">Rejected / Failed</x-ui.badge>
            <x-ui.badge variant="info" dot icon="info">Submitted</x-ui.badge>
            <x-ui.badge variant="neutral" dot icon="inventory_2">Archived</x-ui.badge>
        </div>
    </x-ui.card>

    <!-- 4. Stat Cards -->
    <div>
        <h3 class="text-base font-bold text-on-surface mb-3 font-display">4. Stat Cards (Metrics & KPI)</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-ui.stat-card title="Total Abstracts" value="184" icon="description" change="+12%" trend="up" description="vs last edition" color="primary" />
            <x-ui.stat-card title="Verified Payments" value="IDR 142.5M" icon="payments" change="+8.4%" trend="up" description="78 presenters" color="secondary" />
            <x-ui.stat-card title="Registered Attendees" value="320" icon="groups" change="+24" trend="up" description="14 countries" color="tertiary" />
            <x-ui.stat-card title="Pending Reviews" value="18" icon="pending_actions" change="-5" trend="down" description="Assigned to reviewers" color="error" />
        </div>
    </div>

    <!-- 5. Form Controls -->
    <x-ui.card title="5. Form Controls & Inputs" icon="input">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-ui.input label="Participant Full Name" placeholder="e.g. Prof. Dr. Budi Prasetyo" iconLeft="person" required hint="Used for printed certificate" />
            
            <x-ui.input label="Email Address" type="email" placeholder="name@institution.ac.id" iconLeft="mail" required />

            <x-ui.select label="Participation Role" required iconLeft="badge">
                <option value="oral">Oral Presenter (Onsite / Online)</option>
                <option value="poster">Poster Presenter</option>
                <option value="listener">Attendee / Listener</option>
            </x-ui.select>

            <x-ui.input label="Input with Error" value="invalid-email" error="Please enter a valid institution email" iconLeft="error" />

            <div class="md:col-span-2">
                <x-ui.textarea label="Abstract Text" placeholder="Paste your abstract here (min 150 words)..." rows="3" maxlength="300" hint="Character count counter powered by Alpine.js" />
            </div>

            <div class="md:col-span-2">
                <x-ui.file-upload label="Upload Full Paper (PDF / DOCX)" accept=".pdf,.docx" maxSize="10 MB" hint="Visual dropzone with filename and byte size display" />
            </div>

            <div class="space-y-3">
                <x-ui.checkbox label="I agree to ASIC 2027 Terms & Conditions" description="Submission will be published in official conference proceedings." checked />
                <x-ui.checkbox label="Field Trip Package" description="Join the Minangkabau Agricultural Excursion on Day 3." />
            </div>

            <div class="space-y-4">
                <x-ui.toggle label="Instant Email Notification" description="Receive alert when review decision is published" checked />
                <x-ui.toggle label="WhatsApp Broadcast Notification" description="Receive WhatsApp alert for payment status updates" />
            </div>
        </div>
    </x-ui.card>

    <!-- 6. Radio Cards -->
    <x-ui.card title="6. Radio Cards (Participant Tier / Nationality Selection)" icon="credit_card">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4" x-data="{ selectedType: 'oral' }">
            <x-ui.radio-card name="ticket_type" value="oral" title="Oral Presenter" description="Present your paper in parallel technical session" price="IDR 1.500.000" icon="record_voice_over" model="selectedType" />
            <x-ui.radio-card name="ticket_type" value="poster" title="Poster Presenter" description="Showcase poster board in exhibition hall" price="IDR 1.200.000" icon="aspect_ratio" model="selectedType" />
            <x-ui.radio-card name="ticket_type" value="listener" title="Conference Listener" description="Access all keynote sessions & proceedings" price="IDR 750.000" icon="hearing" model="selectedType" />
        </div>
    </x-ui.card>

    <!-- 7. Interactive Modals & Tabs -->
    <x-ui.card title="7. Interactive Modals & Tabs (Alpine.js)" icon="tab">
        <div class="space-y-6">
            <div class="flex items-center gap-3">
                <x-ui.button variant="primary" iconLeft="open_in_browser" @click="$dispatch('open-modal', 'demo-modal')">
                    Open Demo Modal
                </x-ui.button>
                <x-ui.button variant="secondary" iconLeft="notifications" @click="$store.toast.add('Sample Error Toast Notification', 'error')">
                    Toast Error
                </x-ui.button>
                <x-ui.button variant="gold" iconLeft="warning" @click="$store.toast.add('Payment deadline approaching!', 'warning')">
                    Toast Warning
                </x-ui.button>
            </div>

            <!-- Tabs Demo -->
            <x-ui.tabs :tabs="[
                ['id' => 'qris', 'label' => 'QRIS / Virtual Account', 'icon' => 'qr_code_2'],
                ['id' => 'paypal', 'label' => 'PayPal / International Card', 'icon' => 'credit_card'],
                ['id' => 'bank', 'label' => 'Direct Bank Transfer', 'icon' => 'account_balance']
            ]">
                <div x-show="activeTab === 'qris'" class="p-4 rounded-xl bg-surface-container-low border border-surface-container-highest">
                    <p class="font-bold text-sm text-on-surface">QRIS Instant Payment</p>
                    <p class="text-xs text-on-surface-variant mt-1">Scan using any Indonesian mobile banking app (BCA, Mandiri, BNI, BRI) or e-Wallet (GoPay, OVO, Dana).</p>
                </div>
                <div x-show="activeTab === 'paypal'" class="p-4 rounded-xl bg-surface-container-low border border-surface-container-highest" style="display: none;">
                    <p class="font-bold text-sm text-on-surface">PayPal Checkout (USD)</p>
                    <p class="text-xs text-on-surface-variant mt-1">For international participants. Secure transfer with invoice reference #PAY-ASIC-2027.</p>
                </div>
                <div x-show="activeTab === 'bank'" class="p-4 rounded-xl bg-surface-container-low border border-surface-container-highest" style="display: none;">
                    <p class="font-bold text-sm text-on-surface">Manual Bank Transfer</p>
                    <p class="text-xs text-on-surface-variant mt-1">Bank Nagari / BNI Cabang Universitas Andalas. Upload transfer proof for treasurer verification.</p>
                </div>
            </x-ui.tabs>
        </div>
    </x-ui.card>

    <!-- Modal Definition -->
    <x-ui.modal name="demo-modal" title="Modal Evaluation Dialog" icon="task_alt">
        <div class="space-y-4">
            <p class="text-xs text-on-surface leading-relaxed">
                This is a reusable anonymous Blade modal component powered by Alpine.js with smooth transitions, ESC key listeners, and backdrop click dismissal.
            </p>
            <x-ui.alert variant="info" title="Verification Note">
                Once confirmed, an automated notification email will be dispatched to the author.
            </x-ui.alert>
            <x-ui.textarea label="Reviewer Notes" placeholder="Enter feedback for the author..." rows="2" />
        </div>
        <x-slot:footer>
            <x-ui.button variant="ghost" @click="$dispatch('close-modal', 'demo-modal')">Cancel</x-ui.button>
            <x-ui.button variant="primary" iconLeft="check" @click="$store.toast.add('Evaluation saved successfully!', 'success'); $dispatch('close-modal', 'demo-modal')">Confirm Decision</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    <!-- 8. Table & Pagination -->
    <div>
        <h3 class="text-base font-bold text-on-surface mb-3 font-display">8. Responsive Data Table & Pagination</h3>
        <x-ui.table :headers="['Abstract ID', 'Title & Author', 'Topic', 'Submitted', 'Status', 'Action']" striped>
            <tr>
                <td class="px-5 py-4 font-mono font-bold text-primary">ASIC-ABS-001</td>
                <td class="px-5 py-4">
                    <p class="font-bold text-on-surface">Biochar Application in Tropical Acid Soils</p>
                    <p class="text-[11px] text-on-surface-variant">Prof. Ahmad Fauzi — Universitas Andalas</p>
                </td>
                <td class="px-5 py-4">Soil Science & Agronomy</td>
                <td class="px-5 py-4 font-mono text-[11px]">2027-04-12</td>
                <td class="px-5 py-4">
                    <x-ui.badge variant="success" dot>Accepted</x-ui.badge>
                </td>
                <td class="px-5 py-4">
                    <x-ui.button size="sm" variant="outline" iconLeft="visibility">Detail</x-ui.button>
                </td>
            </tr>
            <tr>
                <td class="px-5 py-4 font-mono font-bold text-primary">ASIC-ABS-002</td>
                <td class="px-5 py-4">
                    <p class="font-bold text-on-surface">Smart Pest Monitoring using IoT Sensors</p>
                    <p class="text-[11px] text-on-surface-variant">Dr. Kenji Tanaka — Kyoto University</p>
                </td>
                <td class="px-5 py-4">Precision Agriculture</td>
                <td class="px-5 py-4 font-mono text-[11px]">2027-04-14</td>
                <td class="px-5 py-4">
                    <x-ui.badge variant="amber" dot>Under Review</x-ui.badge>
                </td>
                <td class="px-5 py-4">
                    <x-ui.button size="sm" variant="outline" iconLeft="visibility">Detail</x-ui.button>
                </td>
            </tr>
            <tr>
                <td class="px-5 py-4 font-mono font-bold text-primary">ASIC-ABS-003</td>
                <td class="px-5 py-4">
                    <p class="font-bold text-on-surface">Post-Harvest Losses in Smallholder Cassava</p>
                    <p class="text-[11px] text-on-surface-variant">Siti Nurhaliza, M.Sc — IPB University</p>
                </td>
                <td class="px-5 py-4">Food Security & Supply Chain</td>
                <td class="px-5 py-4 font-mono text-[11px]">2027-04-15</td>
                <td class="px-5 py-4">
                    <x-ui.badge variant="warning" dot>Revision</x-ui.badge>
                </td>
                <td class="px-5 py-4">
                    <x-ui.button size="sm" variant="outline" iconLeft="visibility">Detail</x-ui.button>
                </td>
            </tr>
            <x-slot:footer>
                <div class="w-full">
                    <x-ui.pagination :current-page="1" :total-pages="5" :total-items="48" :from="1" :to="3" />
                </div>
            </x-slot:footer>
        </x-ui.table>
    </div>

    <!-- 9. Timeline, QR Box, & Empty State -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Review Timeline -->
        <x-ui.card title="Review Milestone" icon="timeline">
            <x-ui.timeline :steps="[
                ['title' => 'Abstract Submitted', 'date' => '12 Apr', 'status' => 'completed', 'description' => 'Original manuscript submitted by author.'],
                ['title' => 'Peer Review Evaluation', 'date' => '18 Apr', 'status' => 'completed', 'description' => 'Reviewed by 2 scientific committee members.'],
                ['title' => 'Official LoA Issued', 'date' => '22 Apr', 'status' => 'current', 'description' => 'Letter of Acceptance available for download.'],
                ['title' => 'Camera Ready Upload', 'date' => '10 May', 'status' => 'upcoming', 'description' => 'Submit full paper for conference proceeding.']
            ]" />
        </x-ui.card>

        <!-- QR Box -->
        <x-ui.card title="Attendance QR" icon="qr_code">
            <x-ui.qr-box code="ASIC-2027-0092" title="Presenter On-site Check-in" subtitle="Show this QR at reception counter on 24 August 2027">
                <x-ui.button size="sm" variant="outline" iconLeft="download">Save Image</x-ui.button>
            </x-ui.qr-box>
        </x-ui.card>

        <!-- Empty State -->
        <x-ui.card title="Empty State Check" icon="inbox">
            <x-ui.empty-state icon="mail" title="No Pending Broadcasts" description="All scheduled announcements have been processed by the queue.">
                <x-ui.button size="sm" variant="primary" iconLeft="add">Create New</x-ui.button>
            </x-ui.empty-state>
        </x-ui.card>
    </div>
</div>
@endsection

