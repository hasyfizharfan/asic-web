<?php

namespace App\Providers;

use App\Models\Registration;
use App\Models\Role;
use App\Observers\RegistrationObserver;
use App\Services\PublicContent;
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

        // Nav/footer publik: tautan halaman hanya untuk halaman yang punya isi terpublikasi.
        View::composer(['layouts.public', 'public._topnav'], function ($view): void {
            if (! array_key_exists('publicPages', $view->getData())) {
                $content = app(PublicContent::class);
                $view->with('publicPages', $content->availablePages($content->activeEvent()));
            }
        });

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
            $user = auth()->user();
            $nav = collect(config('portal-nav.reviewer', []))
                ->filter(fn (array $item) => app(PermissionMatrix::class)->allows($user, $item['permission'], 'view_only'))
                ->map(fn (array $item) => [...$item, 'label' => __($item['label'])])->values()->all();
            $view->with([
                'role' => 'reviewer',
                'roleName' => __('asic-reviewer.portal_title') ?? 'Abstract Reviewer',
                'nav' => $nav,
            ]);
        });
        View::composer('secretariat.*', function ($view): void {
            $user = auth()->user();
            $nav = collect(config('portal-nav.secretariat', []))
                ->filter(fn (array $item) => app(PermissionMatrix::class)->allows($user, $item['permission'], 'view_only'))
                ->map(fn (array $item) => [...$item, 'label' => __($item['label'])])->values()->all();
            $view->with(['secNav' => $nav, 'nav' => $nav, 'role' => 'secretariat', 'roleName' => 'Sekretariat']);
        });
        View::composer('treasurer.*', function ($view): void {
            $view->with('treasurerNav', [
                ['label' => __('asic-nav.dashboard'), 'route' => 'portal.treasurer.dashboard', 'icon' => 'space_dashboard'],
                ['label' => __('asic-nav.tariffs'), 'route' => 'portal.treasurer.tariffs', 'icon' => 'price_change'],
                ['label' => __('asic-nav.payment_queue'), 'route' => 'portal.treasurer.payments.index', 'icon' => 'receipt_long'],
                ['label' => __('asic-treasurer.receipt.title'), 'route' => 'portal.treasurer.receipts.preview', 'icon' => 'receipt'],
                ['label' => __('asic-nav.refund_requests'), 'route' => 'portal.treasurer.refunds', 'icon' => 'currency_exchange'],
            ]);
        });
        View::composer('admin.*', function ($view): void {
            $user = auth()->user();
            $nav = collect(config('portal-nav.admin', []))
                ->filter(fn (array $item) => app(PermissionMatrix::class)->allows($user, $item['permission'], 'view_only'))
                ->map(fn (array $item) => [...$item, 'label' => __($item['label'])])->values()->all();
            $view->with(['adminNav' => $nav, 'nav' => $nav]);
        });
    }
}
