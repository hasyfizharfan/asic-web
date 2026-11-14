<?php

namespace App\Support;

use App\Models\Role;
use App\Models\UserAccount;
use Illuminate\Support\Facades\DB;

class PermissionMatrix
{
    public const LEVELS = ['full_access', 'view_only', 'restricted'];

    /**
     * @return array<int, array{id: string, title: string, tag: string, description: string}>
     */
    public static function capabilities(): array
    {
        return [
            ['id' => 'submit_abstract', 'title' => 'Submit abstract', 'tag' => 'Manuscripts', 'description' => 'Submit or update an abstract for an owned registration'],
            ['id' => 'view_abstracts', 'title' => 'View abstracts', 'tag' => 'Manuscripts', 'description' => 'View submitted abstracts subject to registration ownership'],
            ['id' => 'review_abstracts', 'title' => 'Review abstracts', 'tag' => 'Peer Review', 'description' => 'View the abstract review queue and review history'],
            ['id' => 'accept_reject', 'title' => 'Accept/Reject', 'tag' => 'Decisions', 'description' => 'Record abstract acceptance, rejection, or revision decisions'],
            ['id' => 'pay_registration', 'title' => 'Pay registration', 'tag' => 'Treasury', 'description' => 'Create registrations and view owned invoices'],
            ['id' => 'verify_payment', 'title' => 'Verify payment', 'tag' => 'Treasury', 'description' => 'View and manage invoice payment status'],
            ['id' => 'manage_tariffs', 'title' => 'Manage tariffs', 'tag' => 'Treasury', 'description' => 'Configure registration fee tariffs'],
            ['id' => 'scan_attendance_qr', 'title' => 'Scan attendance QR', 'tag' => 'Onsite Desk', 'description' => 'Check in participants and manage attendance'],
            ['id' => 'manage_website_content', 'title' => 'Manage website content', 'tag' => 'CMS / Portal', 'description' => 'Manage public website content'],
            ['id' => 'manage_users', 'title' => 'Manage users', 'tag' => 'Governance', 'description' => 'Manage user accounts, roles, and access settings'],
            ['id' => 'manage_document_templates', 'title' => 'Manage document templates', 'tag' => 'Publishing', 'description' => 'Manage document templates'],
            ['id' => 'manage_registrations', 'title' => 'Manage registrations', 'tag' => 'Registration', 'description' => 'View registrations, limited to owned records unless full access is granted'],
            ['id' => 'manage_participant_files', 'title' => 'Manage participant files', 'tag' => 'Manuscripts', 'description' => 'View or manage participant files subject to registration ownership'],
            ['id' => 'upload_participant_files', 'title' => 'Upload participant files', 'tag' => 'Manuscripts', 'description' => 'Upload files for owned registrations or manage uploads with delegated access'],
            ['id' => 'manage_sessions', 'title' => 'Manage sessions', 'tag' => 'Schedule', 'description' => 'View and manage event sessions'],
            ['id' => 'manage_digital_signatures', 'title' => 'Manage digital signatures', 'tag' => 'Publishing', 'description' => 'View or manage role-based digital signatures and stamps'],
        ];
    }

    /**
     * Builds the seeded matrix using the role IDs currently stored in the database.
     *
     * @return array<string, array<string, string>>
     */
    public static function defaultMatrix(): array
    {
        $roleIds = Role::query()->pluck('id_role', 'nama_role');

        $defaultsByRoleName = [
            'submit_abstract' => [Role::ROLE_PESERTA => 'full_access', Role::ROLE_ADMIN => 'full_access'],
            'view_abstracts' => [Role::ROLE_PESERTA => 'view_only', Role::ROLE_SEKRETARIAT => 'full_access', Role::ROLE_TIM_ABSTRAK => 'full_access', Role::ROLE_ADMIN => 'full_access'],
            'review_abstracts' => [Role::ROLE_SEKRETARIAT => 'view_only', Role::ROLE_TIM_ABSTRAK => 'full_access', Role::ROLE_ADMIN => 'full_access'],
            'accept_reject' => [Role::ROLE_TIM_ABSTRAK => 'full_access', Role::ROLE_ADMIN => 'full_access'],
            'pay_registration' => [Role::ROLE_PESERTA => 'full_access', Role::ROLE_SEKRETARIAT => 'view_only', Role::ROLE_BENDAHARA => 'view_only', Role::ROLE_ADMIN => 'full_access'],
            'verify_payment' => [Role::ROLE_SEKRETARIAT => 'view_only', Role::ROLE_BENDAHARA => 'full_access', Role::ROLE_ADMIN => 'full_access'],
            'manage_tariffs' => [Role::ROLE_BENDAHARA => 'full_access', Role::ROLE_ADMIN => 'full_access'],
            'scan_attendance_qr' => [Role::ROLE_PESERTA => 'view_only', Role::ROLE_SEKRETARIAT => 'full_access', Role::ROLE_BENDAHARA => 'view_only', Role::ROLE_ADMIN => 'full_access'],
            'manage_website_content' => [Role::ROLE_SEKRETARIAT => 'full_access', Role::ROLE_ADMIN => 'full_access'],
            'manage_users' => [Role::ROLE_ADMIN => 'full_access'],
            'manage_document_templates' => [Role::ROLE_SEKRETARIAT => 'view_only', Role::ROLE_BENDAHARA => 'view_only', Role::ROLE_ADMIN => 'full_access'],
            'manage_registrations' => [Role::ROLE_PESERTA => 'view_only', Role::ROLE_SEKRETARIAT => 'full_access', Role::ROLE_BENDAHARA => 'view_only', Role::ROLE_TIM_ABSTRAK => 'view_only', Role::ROLE_ADMIN => 'full_access'],
            'manage_participant_files' => [Role::ROLE_PESERTA => 'view_only', Role::ROLE_SEKRETARIAT => 'full_access', Role::ROLE_TIM_ABSTRAK => 'full_access', Role::ROLE_ADMIN => 'full_access'],
            'upload_participant_files' => [Role::ROLE_PESERTA => 'full_access', Role::ROLE_SEKRETARIAT => 'full_access', Role::ROLE_TIM_ABSTRAK => 'full_access', Role::ROLE_ADMIN => 'full_access'],
            'manage_sessions' => [Role::ROLE_PESERTA => 'view_only', Role::ROLE_SEKRETARIAT => 'full_access', Role::ROLE_BENDAHARA => 'view_only', Role::ROLE_TIM_ABSTRAK => 'view_only', Role::ROLE_ADMIN => 'full_access'],
            'manage_digital_signatures' => [Role::ROLE_PESERTA => 'view_only', Role::ROLE_SEKRETARIAT => 'full_access', Role::ROLE_BENDAHARA => 'view_only', Role::ROLE_TIM_ABSTRAK => 'view_only', Role::ROLE_ADMIN => 'full_access'],
        ];

        $matrix = [];
        foreach (self::capabilities() as $capability) {
            $permission = $capability['id'];
            $matrix[$permission] = [];
            foreach ($roleIds as $roleName => $roleId) {
                $matrix[$permission][(string) $roleId] = $defaultsByRoleName[$permission][$roleName] ?? 'restricted';
            }
        }

        return $matrix;
    }

    /**
     * Returns only the matrix persisted in the database. Missing rows deny access.
     *
     * @return array<string, array<string, string>>
     */
    public function activeMatrix(): array
    {
        $storedMatrix = DB::table('role_permission_matrix')->where('id', 1)->value('matrix');

        if (! is_string($storedMatrix) || $storedMatrix === '') {
            return [];
        }

        $matrix = json_decode($storedMatrix, true, 512, JSON_THROW_ON_ERROR);

        return is_array($matrix) ? $matrix : [];
    }

    public function levelFor(UserAccount $user, string $permission): string
    {
        if (! in_array($permission, array_column(self::capabilities(), 'id'), true)) {
            return 'restricted';
        }

        $level = $this->activeMatrix()[$permission][(string) $user->id_role] ?? 'restricted';

        return in_array($level, self::LEVELS, true) ? $level : 'restricted';
    }

    public function allows(UserAccount $user, string $permission, string $minimum = 'full_access'): bool
    {
        if (! in_array($minimum, ['full_access', 'view_only'], true)) {
            return false;
        }

        $level = $this->levelFor($user, $permission);

        return match ($minimum) {
            'view_only' => in_array($level, ['view_only', 'full_access'], true),
            default => $level === 'full_access',
        };
    }
}
