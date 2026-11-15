<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentVerification extends Model
{
    use HasFactory;

    protected $table = 'payment_verification';
    protected $primaryKey = 'id_verification';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = ['id_payment_proof','id_treasurer','tanggal_verifikasi','hasil_verifikasi','alasan_penolakan'];

    protected function casts(): array
    {
        return ['tanggal_verifikasi' => 'datetime'];
    }

    public function paymentProof(): BelongsTo
    {
        return $this->belongsTo(PaymentProof::class, 'id_payment_proof', 'id_payment_proof');
    }

    public function treasurer(): BelongsTo
    {
        return $this->belongsTo(UserAccount::class, 'id_treasurer', 'id_user');
    }
}
