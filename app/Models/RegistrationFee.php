<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistrationFee extends Model
{
    use HasFactory;

    protected $table = 'registration_fee';

    protected $primaryKey = 'id_fee';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_event',
        'id_period',
        'registration_type',
        'attendance_mode',
        'tujuan_publikasi',
        'amount',
        'discount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'discount' => 'decimal:2',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'id_event', 'id_event');
    }

    public function registrationPeriod(): BelongsTo
    {
        return $this->belongsTo(RegistrationPeriod::class, 'id_period', 'id_period');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'id_fee', 'id_fee');
    }

    public const REGISTRATION_TYPES = ['Student', 'Regular', 'Committee'];

    public const ATTENDANCE_MODES = ['Daring', 'Luring'];

    public const PUBLICATION_TARGETS = ['Prosiding', 'Jurnal', 'Tidak'];

    /**
     * Tarif dianggap "terpakai" jika sudah dirujuk oleh minimal satu invoice.
     */
    public function isUsedByInvoice(): bool
    {
        return $this->invoices()->exists();
    }

    /**
     * Nominal bersih (amount - discount), tidak pernah negatif.
     * Dihitung dalam satuan sen agar bebas galat floating point.
     */
    public function finalAmount(): string
    {
        $cents = max(0, (int) round(((float) $this->amount) * 100) - (int) round(((float) $this->discount) * 100));

        return number_format($cents / 100, 2, '.', '');
    }
}
