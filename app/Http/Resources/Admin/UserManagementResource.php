<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserManagementResource extends JsonResource
{
    /**
     * Transform the resource into an array for User & Role Management UI.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $participant = $this->participant;
        $roleName = $this->role?->nama_role ?? 'Peserta';

        // Format nama lengkap & gelar
        $fullName = $participant?->full_name;
        if (!$fullName || trim($fullName) === '') {
            $fullName = explode('@', $this->email)[0];
        }

        // Hitung inisial avatar (misal: "Prof. Dr. Ir. Helmi" -> "H", "Budi Raharjo" -> "BR")
        $initials = $this->computeInitials($fullName);

        // Badge khusus sesuai peran (misal: "FINANCE PIN" untuk Bendahara, "SUPERUSER" untuk Admin)
        $badge = null;
        if ($roleName === 'Admin') {
            $badge = 'SUPERUSER';
        } elseif ($roleName === 'Bendahara') {
            $badge = 'FINANCE PIN';
        }

        // Subtext di samping email (NIP / Paper ID / Rek / Track / Key ID)
        $metaSubtext = $this->resolveMetaSubtext($participant, $roleName);

        // Normalisasi nama peran ke bahasa Inggris sesuai visual UI
        $uiRoleName = match ($roleName) {
            'Peserta' => 'Participant',
            'Sekretariat' => 'Secretariat',
            'Bendahara' => 'Treasurer',
            'Tim Abstrak' => 'Abstract Team',
            default => $roleName,
        };

        return [
            'id_user' => $this->id_user,
            'id_role' => $this->id_role,
            'email' => $this->email,
            'name' => $fullName,
            'initials' => $initials,
            'verified' => true,
            'badge' => $badge,
            'meta_subtext' => $metaSubtext,
            'primary_role' => $uiRoleName,
            'indonesian_role' => $roleName,
            'affiliation' => $participant?->institution ?? '-',
            'status' => $this->status_akun === 'Aktif' ? 'Active' : 'Inactive',
            'status_akun' => $this->status_akun,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'participant' => $participant,
        ];
    }

    /**
     * Helper untuk menghitung inisial avatar.
     */
    protected function computeInitials(string $name): string
    {
        $clean = preg_replace('/^(Prof\.|Dr\.|Ir\.|Drs\.|Dra\.|Mr\.|Ms\.|Mrs\.)\s*/i', '', $name);
        $words = preg_split('/\s+/', trim((string) $clean));

        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }

        return strtoupper(substr($words[0] ?? 'U', 0, 2));
    }

    /**
     * Helper untuk meresolusi informasi sekunder di bawah email pengguna.
     */
    protected function resolveMetaSubtext(?object $participant, string $roleName): string
    {
        if ($participant?->passport_no) {
            return "NIP: {$participant->passport_no}";
        }

        if ($participant?->job_title) {
            return $participant->job_title;
        }

        return match ($roleName) {
            'Admin' => "Key ID: UNAND-SEC-{$this->id_user}",
            'Bendahara' => "Rek: Mandiri TF-00{$this->id_user}",
            'Sekretariat' => 'Scientific Desk',
            'Tim Abstrak' => 'Reviewer Panel',
            default => "Paper ID: ASIC-2026-" . str_pad((string) $this->id_user, 3, '0', STR_PAD_LEFT),
        };
    }
}

