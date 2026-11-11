<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationAddOn extends Model
{
    use HasFactory;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'registration_add_on';

    /**
     * Primary key komposit.
     *
     * @var string|null
     */
    protected $primaryKey = 'id_registration';

    /**
     * Non auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Tidak menggunakan timestamps bawaan Laravel.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Atribut yang dapat diisi.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_registration',
        'id_add_on',
        'jumlah',
        'subtotal',
    ];

    /**
     * Cast atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'subtotal' => 'decimal:2',
        ];
    }

    /**
     * Relasi belongsTo ke Registration.
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'id_registration', 'id_registration');
    }

    /**
     * Relasi belongsTo ke AddOn.
     */
    public function addOn(): BelongsTo
    {
        return $this->belongsTo(AddOn::class, 'id_add_on', 'id_add_on');
    }
}

