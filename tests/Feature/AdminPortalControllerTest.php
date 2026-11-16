<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\PaymentProof;
use App\Models\PaymentVerification;
use App\Models\Role;
use App\Models\UserAccount;
use App\Support\Currency;
use App\Support\PermissionMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

class AdminPortalControllerTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
    }

    public function test_admin_portal_pages_are_controller_backed_and_access_controlled(): void
    {
        $routes = [
            'dashboard' => '/admin/dashboard', 'overview' => '/admin/overview', 'users' => '/admin/users',
            'permissions' => '/admin/permissions', 'signatures' => '/admin/signatures', 'templates' => '/admin/templates',
            'settings' => '/admin/settings', 'archive' => '/admin/archive',
        ];

        foreach ($routes as $name => $uri) {
            $route = app('router')->getRoutes()->getByName('portal.admin.'.$name);
            $this->assertNotNull($route, $name);
            $this->assertStringContainsString('AdminController@', $route->getActionName(), $name);
            $response = $this->as(Role::ROLE_ADMIN)->get($uri);
            $this->assertContains($response->getStatusCode(), $name === 'overview' ? [200, 302] : [200], $name);

            foreach ([Role::ROLE_PESERTA, Role::ROLE_SEKRETARIAT, Role::ROLE_BENDAHARA, Role::ROLE_TIM_ABSTRAK] as $role) {
                $this->as($role)->get($uri)->assertForbidden();
            }
        }

        $this->app['auth']->forgetGuards();
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
        $this->as(Role::ROLE_ADMIN)->get('/admin/overview')->assertRedirect(route('portal.admin.dashboard'));
    }

    public function test_admin_dashboard_uses_report_service_data_and_respects_payment_permission(): void
    {
        $registration = $this->registration('admin-dashboard');
        $this->abstractFor($registration, ['status_abstrak' => 'Diterima']);

        $this->as(Role::ROLE_ADMIN)->get('/admin/dashboard')->assertOk()
            ->assertSee('Registrasi · 1')->assertSee('Abstrak · 1')->assertSee('Periode registrasi aktif');
    }

    public function test_dashboard_payment_totals_match_database_and_are_hidden_without_view_permission(): void
    {
        $this->fee(['amount' => 600000, 'discount' => 0]);
        $verifiedRegistration = $this->registration('admin-paid', ['participation_type' => 'Listener']);
        $verifiedInvoice = Invoice::where('id_registration', $verifiedRegistration->id_registration)->firstOrFail();
        $verifiedInvoice->update(['status_pembayaran' => 'Terverifikasi']);

        $pendingRegistration = $this->registration('admin-pending', ['participation_type' => 'Listener']);
        $pendingInvoice = Invoice::where('id_registration', $pendingRegistration->id_registration)->firstOrFail();
        $pendingInvoice->update(['status_pembayaran' => 'Menunggu Verifikasi']);
        $proof = PaymentProof::create(['id_invoice' => $pendingInvoice->id_invoice, 'file_bukti' => 'proof.pdf', 'nominal_transfer' => 600000]);
        PaymentVerification::create(['id_payment_proof' => $proof->id_payment_proof, 'id_treasurer' => $this->user(Role::ROLE_BENDAHARA)->id_user, 'hasil_verifikasi' => 'Valid']);

        $this->as(Role::ROLE_ADMIN)->get('/admin/dashboard')->assertOk()
            ->assertSee(Currency::format(600000, 'IDR'))->assertSee('Belum lunas')->assertSee('Menunggu verifikasi');

        $admin = $this->user(Role::ROLE_ADMIN, '.without-payment-view');
        $paymentView = Role::where('nama_role', Role::ROLE_ADMIN)->value('id_role');
        $matrix = app(PermissionMatrix::class)->activeMatrix();
        $matrix['verify_payment'][(string) $paymentView] = 'restricted';
        DB::table('role_permission_matrix')->where('id', 1)->update(['matrix' => json_encode($matrix)]);
        $this->actingAs($admin, 'sanctum')->get('/admin/dashboard')->assertOk()->assertDontSee('Belum lunas')->assertDontSee('IDR 600.000,00');
    }

    public function test_admin_views_contain_no_frontend_fixture_content_or_preview_routes(): void
    {
        foreach (File::allFiles(resource_path('views/admin')) as $file) {
            $path = $file->getPathname();
            $contents = file_get_contents($path);
            foreach (["route('fe.", 'TBA', 'ASIC 2025', 'ABS-0104', 'PAY-0104', 'Farhan Harahap'] as $fixture) {
                $this->assertStringNotContainsString($fixture, $contents, $path);
            }
        }
    }

    public function test_admin_user_search_is_sqlite_portable_and_returns_governance_data(): void
    {
        $this->registration('admin-search', [], ['first_name' => 'Alya', 'last_name' => 'Putri']);

        $response = $this->as(Role::ROLE_ADMIN)->getJson('/api/admin/users?search=Alya%20Putri');
        $response->assertOk()->assertJsonPath('summary.total_system_users.count', 2)
            ->assertJsonPath('governance_matrix.roles.0.name', Role::ROLE_PESERTA)
            ->assertJsonPath('governance_matrix.capabilities.0.id', 'submit_abstract')
            ->assertJsonPath('users.0.name', 'Alya Putri');
    }

    public function test_user_mutations_are_audited_without_passwords_and_non_admin_is_forbidden(): void
    {
        $admin = $this->user(Role::ROLE_ADMIN, '.audit-actor');
        $participantRole = Role::where('nama_role', Role::ROLE_PESERTA)->value('id_role');
        $this->actingAs($admin, 'sanctum')->postJson('/api/admin/users', [
            'id_role' => $participantRole, 'email' => 'created-audit@example.test', 'password' => 'SecretPassword-456',
            'status_akun' => 'Aktif', 'full_name' => 'Audit User',
        ])->assertCreated();

        $target = UserAccount::where('email', 'created-audit@example.test')->firstOrFail();
        $this->putJson('/api/admin/users/'.$target->id_user, [
            'id_role' => $participantRole, 'email' => 'updated-audit@example.test', 'password' => 'AnotherSecret-789',
            'status_akun' => 'Aktif', 'full_name' => 'Audit User Updated',
        ])->assertOk();
        $this->patchJson('/api/admin/users/'.$target->id_user.'/toggle-status')->assertOk();
        $this->postJson('/api/admin/users/batch-action', ['action' => 'activate', 'user_ids' => [$target->id_user]])->assertOk();
        $this->deleteJson('/api/admin/users/'.$target->id_user)->assertOk();

        $actions = AuditLog::query()->whereIn('action', ['USER_CREATED', 'USER_UPDATED', 'USER_STATUS_TOGGLED', 'USER_BATCH_ACTION', 'USER_DELETED'])->pluck('action')->all();
        foreach (['USER_CREATED', 'USER_UPDATED', 'USER_STATUS_TOGGLED', 'USER_BATCH_ACTION', 'USER_DELETED'] as $action) {
            $this->assertContains($action, $actions);
        }
        $serialized = AuditLog::query()->get()->toJson();
        $this->assertStringNotContainsString('SecretPassword-456', $serialized);
        $this->assertStringNotContainsString('AnotherSecret-789', $serialized);

        $this->as(Role::ROLE_PESERTA)->postJson('/api/admin/users/batch-action', ['action' => 'activate', 'user_ids' => [1]])->assertForbidden();
    }

    public function test_admin_cannot_mutate_own_account_or_remove_last_active_admin_in_batch(): void
    {
        $admin = $this->user(Role::ROLE_ADMIN, '.guard-actor');
        $otherAdmin = $this->user(Role::ROLE_ADMIN, '.guard-target');
        $participantRole = Role::where('nama_role', Role::ROLE_PESERTA)->value('id_role');
        $this->actingAs($admin, 'sanctum')->putJson('/api/admin/users/'.$admin->id_user, [
            'id_role' => $participantRole, 'email' => $admin->email, 'status_akun' => 'Aktif',
        ])->assertForbidden();
        $this->deleteJson('/api/admin/users/'.$admin->id_user)->assertForbidden();
        $this->patchJson('/api/admin/users/'.$admin->id_user.'/toggle-status')->assertForbidden();
        $this->postJson('/api/admin/users/batch-action', ['action' => 'delete', 'user_ids' => [$admin->id_user]])->assertForbidden();

        $admin->update(['status_akun' => 'Nonaktif']);
        $this->postJson('/api/admin/users/batch-action', ['action' => 'deactivate', 'user_ids' => [$otherAdmin->id_user]])->assertUnprocessable();
        $this->assertSame('Aktif', $otherAdmin->fresh()->status_akun);
    }

    public function test_permission_matrix_changes_and_audit_log_are_persistent_filterable_and_paginated(): void
    {
        $matrix = app(PermissionMatrix::class)->activeMatrix();
        $this->as(Role::ROLE_ADMIN)->postJson('/api/admin/roles-permissions', ['matrix' => $matrix, 'audit_note' => 'Review ACL'])->assertOk();
        $this->postJson('/api/admin/roles-permissions/reset')->assertOk();

        $this->getJson('/api/admin/roles-permissions/audit-log?action=UPDATE_MATRIX&per_page=1')->assertOk()
            ->assertJsonPath('data.0.action', 'UPDATE_MATRIX')->assertJsonPath('data.0.note', 'Review ACL')
            ->assertJsonPath('pagination.per_page', 1)->assertJsonPath('pagination.total', 1);
        $this->as(Role::ROLE_PESERTA)->getJson('/api/admin/roles-permissions/audit-log')->assertForbidden();
    }

    public function test_signature_admin_page_uses_its_capability_and_disables_upload_for_view_only(): void
    {
        $admin = $this->user(Role::ROLE_ADMIN, '.signature-view');
        $matrix = app(PermissionMatrix::class)->activeMatrix();
        $adminRoleId = (string) $admin->id_role;
        $matrix['manage_digital_signatures'][$adminRoleId] = 'view_only';
        DB::table('role_permission_matrix')->where('id', 1)->update(['matrix' => json_encode($matrix)]);

        $this->actingAs($admin, 'sanctum')->get('/admin/signatures')->assertOk()
            ->assertSee('Akses hanya lihat')->assertDontSee('name="file_ttd"', false);
    }

    /** Fase 2 (KF-29): arsip nyata; prosiding/DOI tetap "Belum tersedia". */
    public function test_archive_page_lists_editions_and_keeps_proceedings_unavailable(): void
    {
        $this->as(Role::ROLE_ADMIN)->get('/admin/archive')->assertOk()
            ->assertSee(__('asic-documents.archive.proceedings_unavailable'))->assertDontSee("route('fe.", false);
    }

    /** Fase 2 (KF-26): dua kelompok template terhubung ke API (tidak lagi "Belum tersedia"). */
    public function test_document_templates_show_both_groups(): void
    {
        $this->as(Role::ROLE_ADMIN)->get('/admin/templates')->assertOk()
            ->assertSee(__('asic-documents.admin.tab_issued'))->assertSee(__('asic-documents.admin.tab_downloads'))
            ->assertSee('/api/document-templates', false);
    }

    public function test_website_settings_are_unavailable_without_backend_data(): void
    {
        $this->as(Role::ROLE_ADMIN)->get('/admin/settings')->assertOk()
            ->assertSee('Pengaturan website')->assertSee('Belum tersedia')->assertSee('disabled', false)
            ->assertDontSee('TBA');
    }
}
