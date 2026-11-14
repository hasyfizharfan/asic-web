@extends('layouts.portal', [
    'role' => 'participant'
])

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{
    notifications: [
        { id: 1, title: 'Official LoA Ready for Download', desc: 'Your Letter of Acceptance (LoA) for paper ASIC-2027-ABS-0089 is now signed and available in Documents.', time: '2 hours ago', unread: true, type: 'academic', icon: 'verified' },
        { id: 2, title: 'Payment Verification Successful', desc: 'Treasurer verified your QRIS settlement for IDR 1.500.000. Official receipt issued.', time: 'Yesterday', unread: true, type: 'payment', icon: 'receipt_long' },
        { id: 3, title: 'Welcome to ASIC 2027 Portal', desc: 'Your presenter account has been activated. Please check your submission timeline.', time: '3 days ago', unread: false, type: 'system', icon: 'celebration' }
    ],
    markAllRead() {
        this.notifications.forEach(n => n.unread = false);
        $store.toast.add('All notifications marked as read', 'info');
    }
}">
    <x-layout.page-header title="Notifications & Broadcasts" subtitle="System alerts, committee updates, and schedule announcements" :breadcrumbs="[
        ['label' => 'Portal', 'url' => route('fe.participant.dashboard')],
        ['label' => 'Notifications']
    ]">
        <x-ui.button size="sm" variant="outline" iconLeft="done_all" @click="markAllRead()">
            Mark All as Read
        </x-ui.button>
    </x-layout.page-header>

    <div class="space-y-3">
        <template x-for="item in notifications" :key="item.id">
            <div class="p-4 rounded-xl border transition-all flex items-start gap-4"
                 :class="item.unread ? 'bg-surface-container-lowest border-primary/40 shadow-xs' : 'bg-surface-container-low/50 border-surface-container-highest opacity-85'">
                
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                     :class="item.unread ? 'bg-primary-fixed text-primary' : 'bg-surface-container-high text-on-surface-variant'">
                    <span class="material-symbols-outlined text-[20px]" x-text="item.icon"></span>
                </div>

                <div class="flex-1 space-y-1">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-on-surface font-display" x-text="item.title"></span>
                            <span x-show="item.unread" class="w-2 h-2 rounded-full bg-primary"></span>
                        </div>
                        <span class="text-[11px] text-on-surface-variant font-mono" x-text="item.time"></span>
                    </div>
                    <p class="text-xs text-on-surface-variant leading-relaxed" x-text="item.desc"></p>
                </div>

                <button type="button" @click="item.unread = false" x-show="item.unread" class="p-1 rounded-md text-on-surface-variant hover:text-primary transition" title="Mark as read">
                    <span class="material-symbols-outlined text-[16px]">check</span>
                </button>
            </div>
        </template>
    </div>
</div>
@endsection

