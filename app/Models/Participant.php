<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Participant extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database.
     *
     * @var string
     */
    protected $table = 'participant';

    /**
     * Primary key tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_participant';

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
     * Tabel participant tidak memiliki created_at dan updated_at.
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
        'id_user',
        'prefix',
        'first_name',
        'last_name',
        'passport_no',
        'job_title',
        'phone',
        'institution',
        'address',
        'city',
        'country',
        'postcode',
    ];

    /**
     * Relasi belongsTo ke UserAccount (1 Participant dimiliki oleh 1 UserAccount).
     *
     * @return BelongsTo<UserAccount, $this>
     */
    public function userAccount(): BelongsTo
    {
        return $this->belongsTo(UserAccount::class, 'id_user', 'id_user');
    }

    /**
     * Accessor untuk mendapatkan nama lengkap beserta sapaan (prefix).
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([$this->prefix, $this->first_name, $this->last_name]);
        return implode(' ', $parts);
    }
}

