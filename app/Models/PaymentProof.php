<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PaymentProof extends Model
{
    use HasFactory;

    protected $table = 'payment_proof';

    protected $primaryKey = 'id_payment_proof';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = ['id_invoice', 'file_bukti', 'tanggal_upload', 'nominal_transfer', 'catatan_peserta'];

    protected function casts(): array
    {
        return ['tanggal_upload' => 'datetime', 'nominal_transfer' => 'decimal:2'];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'id_invoice', 'id_invoice');
    }

    public function verification(): HasOne
    {
        return $this->hasOne(PaymentVerification::class, 'id_payment_proof', 'id_payment_proof');
    }
}
