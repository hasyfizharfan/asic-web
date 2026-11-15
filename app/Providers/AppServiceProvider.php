<?php

namespace App\Providers;

use App\Models\Registration;
use App\Models\Role;
use App\Observers\RegistrationObserver;
use App\Support\PermissionMatrix;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Registration::observe(RegistrationObserver::class);

        View::composer('participant.*', function ($view): void {
            $user = auth()->user();
            $user?->loadMissing('role');
            $roleKey = match ($user?->role?->nama_role) {
                Role::ROLE_PESERTA => 'participant', Role::ROLE_SEKRETARIAT => 'secretariat',
                Role::ROLE_BENDAHARA => 'treasurer', Role::ROLE_TIM_ABSTRAK => 'reviewer', Role::ROLE_ADMIN => 'admin', default => null,
            };
            $nav = collect(config('portal-nav.'.$roleKey, []))->filter(fn (array $item) => $roleKey !== 'participant' || app(PermissionMatrix::class)->allows($user, $item['permission'], 'view_only'))
                ->map(fn (array $item) => [...$item, 'label' => __($item['label'])])->values()->all();
            $view->with(['participantNav' => $nav, 'nav' => $nav]);
        });
        View::composer('reviewer.*', function ($view): void {
            $view->with([
                'role' => 'reviewer',
                'roleName' => __('asic-reviewer.portal_title') ?? 'Abstract Reviewer',
                'nav' => [
                    ['label' => __('asic-reviewer.queue_title') ?? 'Review Queue', 'route' => 'portal.reviewer.queue', 'icon' => 'rate_review'],
                    ['label' => __('asic-reviewer.profile_title') ?? 'Profile', 'route' => 'portal.reviewer.profile', 'icon' => 'account_circle'],
                ],
            ]);
        });
        View::composer('secretariat.*', function ($view): void {
            $view->with('secNav', [
                ['label' => __('asic-nav.abstracts'), 'route' => 'portal.secretariat.abstracts.index', 'icon' => 'description'],
                ['label' => __('asic-nav.reregistration'), 'route' => 'portal.secretariat.reregistration', 'icon' => 'qr_code_scanner'],
                ['label' => __('asic-nav.attendance_presentation'), 'route' => 'portal.secretariat.attendance', 'icon' => 'how_to_reg'],
                ['label' => __('asic-nav.recap'), 'route' => 'portal.secretariat.recap', 'icon' => 'bar_chart'],
                ['label' => __('asic-nav.announcements'), 'route' => 'portal.secretariat.announcements', 'icon' => 'campaign'],
                ['label' => __('asic-nav.website_content'), 'route' => 'portal.secretariat.content', 'icon' => 'public'],
            ]);
        });
        View::composer('treasurer.*', function ($view): void {
            $view->with('treasurerNav', [
                ['label' => __('asic-nav.tariffs'), 'route' => 'portal.treasurer.tariffs', 'icon' => 'price_change'],
                ['label' => __('asic-nav.payment_queue'), 'route' => 'portal.treasurer.payments.index', 'icon' => 'receipt_long'],
                ['label' => __('asic-nav.refund_requests'), 'route' => 'portal.treasurer.refunds', 'icon' => 'currency_exchange'],
            ]);
        });
        View::composer('admin.*', function ($view): void {
            $view->with('adminNav', [
                ['label' => __('asic-nav.overview'), 'route' => 'portal.admin.overview', 'icon' => 'dashboard'],
                ['label' => __('asic-nav.users_roles'), 'route' => 'portal.admin.users', 'icon' => 'group'],
                ['label' => __('asic-nav.permissions'), 'route' => 'portal.admin.permissions', 'icon' => 'verified_user'],
                ['label' => 'Tanda tangan digital', 'route' => 'portal.admin.signatures', 'icon' => 'draw'],
                ['label' => __('asic-nav.document_templates'), 'route' => 'portal.admin.templates', 'icon' => 'description'],
                ['label' => __('asic-nav.website_settings'), 'route' => 'portal.admin.settings', 'icon' => 'settings'],
                ['label' => __('asic-nav.archive'), 'route' => 'portal.admin.archive', 'icon' => 'inventory_2'],
            ]);
        });
    }
}
