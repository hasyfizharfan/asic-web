@extends('layouts.portal', [
    'role' => 'participant'
])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <x-layout.page-header title="My Account Profile" subtitle="Manage your personal details, academic affiliations, and security settings" :breadcrumbs="[
        ['label' => 'Portal', 'url' => route('fe.participant.dashboard')],
        ['label' => 'Profile']
    ]">
        <x-ui.badge variant="success" dot>Account Active</x-ui.badge>
    </x-layout.page-header>

    {{-- TODO(BE): action route('fe.participant.profile.update') --}}
    <form action="{{ route('fe.participant.profile') }}" method="GET" class="space-y-6" @submit.prevent="$store.toast.add('Profile details updated successfully!', 'success')">
        @csrf
        
        <!-- Profile Identity & Affiliation -->
        <x-ui.card title="Personal & Academic Affiliation" icon="person">
            <div class="space-y-4">
                <div class="flex items-center gap-4 pb-4 border-b border-surface-container-highest">
                    <x-ui.avatar name="Ahmad Fauzi" size="xl" status="online" />
                    <div>
                        <h3 class="text-base font-bold text-on-surface font-display">Prof. Dr. Ir. Ahmad Fauzi, M.Sc.</h3>
                        <p class="text-xs text-on-surface-variant font-mono">ID: ASIC-2027-PRS-0014 • Oral Presenter</p>
                        <p class="text-xs text-primary font-medium mt-0.5">Faculty of Agriculture, Universitas Andalas</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <x-ui.input label="Full Name with Titles" value="Prof. Dr. Ir. Ahmad Fauzi, M.Sc." required hint="Printed verbatim on official certificate" />
                    </div>
                    <x-ui.input label="Email Address" type="email" value="ahmad.fauzi@agr.unand.ac.id" disabled hint="Contact secretariat to modify login email" />
                    <x-ui.input label="WhatsApp Number" value="+62 812-3456-7890" required />
                    <x-ui.input label="Affiliation Institution" value="Universitas Andalas" required />
                    <x-ui.input label="Department / Faculty" value="Department of Soil Science" required />
                    <x-ui.input label="City" value="Padang" required />
                    <x-ui.input label="Country" value="Indonesia" required />
                </div>
            </div>

            <x-slot:footer>
                <div class="w-full flex justify-end">
                    <x-ui.button type="submit" variant="primary" iconLeft="save">
                        Save Profile Changes
                    </x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.card>
    </form>

    <!-- Change Password Form -->
    {{-- TODO(BE): action route('fe.participant.password.update') --}}
    <form action="{{ route('fe.participant.profile') }}" method="GET" class="space-y-4" @submit.prevent="$store.toast.add('Password changed successfully!', 'success')">
        @csrf
        <x-ui.card title="Security & Password" icon="lock">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-ui.input label="Current Password" type="password" placeholder="••••••••" required />
                <x-ui.input label="New Password" type="password" placeholder="••••••••" required />
                <x-ui.input label="Confirm New Password" type="password" placeholder="••••••••" required />
            </div>

            <x-slot:footer>
                <div class="w-full flex justify-end">
                    <x-ui.button type="submit" variant="outline" iconLeft="key">
                        Update Password
                    </x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.card>
    </form>
</div>
@endsection

