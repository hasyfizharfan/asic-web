<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database.
     *
     * @var string
     */
    protected $table = 'role';

    /**
     * Primary key tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_role';

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
     * Tabel role tidak memiliki created_at dan updated_at.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Atribut yang dapat diisi (mass assignable).
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama_role',
        'deskripsi',
    ];

    /**
     * Konstanta 5 Peran (Roles) Sistem Seminar ASIC sesuai PRD & ERD.
     */
    public const ROLE_PESERTA = 'Peserta';
    public const ROLE_SEKRETARIAT = 'Sekretariat';
    public const ROLE_BENDAHARA = 'Bendahara';
    public const ROLE_TIM_ABSTRAK = 'Tim Abstrak';
    public const ROLE_ADMIN = 'Admin';

    /**
     * Relasi hasMany ke UserAccount (1 Role memiliki banyak UserAccount).
     *
     * @return HasMany<UserAccount, $this>
     */
    public function userAccounts(): HasMany
    {
        return $this->hasMany(UserAccount::class, 'id_role', 'id_role');
    }

    /**
     * Relasi hasMany ke DigitalSignature (Tanda Tangan & Stempel Peran).
     *
     * @return HasMany<DigitalSignature, $this>
     */
    public function digitalSignatures(): HasMany
    {
        return $this->hasMany(DigitalSignature::class, 'id_role', 'id_role');
    }
}

