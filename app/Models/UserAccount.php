<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UserAccount extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Nama tabel di database (menggantikan tabel default 'users').
     *
     * @var string
     */
    protected $table = 'user_account';

    /**
     * Primary key tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_user';

    /**
     * Tipe primary key (INT Auto Increment).
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Indikasi primary key bersifat auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Menggunakan created_at dan updated_at.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Atribut yang dapat diisi (mass assignable).
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_role',
        'email',
        'password',
        'status_akun',
    ];

    /**
     * Atribut yang disembunyikan saat serialisasi array / JSON.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Nama kolom remember_token (dikosongkan jika kolom tidak tersedia pada tabel).
     *
     * @var string
     */
    protected $rememberTokenName = '';

    /**
     * Type casting atribut model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi belongsTo ke Role (UserAccount dimiliki oleh 1 Role).
     *
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }

    /**
     * Relasi hasOne ke Participant (1 UserAccount memiliki 1 profil Participant).
     *
     * @return HasOne<Participant, $this>
     */
    public function participant(): HasOne
    {
        return $this->hasOne(Participant::class, 'id_user', 'id_user');
    }

    /**
     * Cek apakah akun berstatus 'Aktif'.
     */
    public function isActive(): bool
    {
        return $this->status_akun === 'Aktif';
    }

    /**
     * Cek apakah akun memiliki peran (role) tertentu.
     *
     * @param string|array<string> $roles
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role?->nama_role, $roles, true);
        }

        return $this->role?->nama_role === $roles;
    }

    /**
     * Cek apakah akun memiliki peran 'Admin'.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ROLE_ADMIN);
    }
}

