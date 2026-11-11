<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoice';
    protected $primaryKey = 'id_invoice';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_registration',
        'id_fee',
        'nomor_invoice',
        'tanggal_invoice',
        'tanggal_jatuh_tempo',
        'total_tagihan',
        'payment_qr_info',
        'status_pembayaran',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_invoice' => 'datetime',
            'tanggal_jatuh_tempo' => 'date',
            'total_tagihan' => 'decimal:2',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'id_registration', 'id_registration');
    }

    public function registrationFee(): BelongsTo
    {
        return $this->belongsTo(RegistrationFee::class, 'id_fee', 'id_fee');
    }

    public function invoiceDetails(): HasMany
    {
        return $this->hasMany(InvoiceDetail::class, 'id_invoice', 'id_invoice');
    }

    public function paymentProofs(): HasMany
    {
        return $this->hasMany(PaymentProof::class, 'id_invoice', 'id_invoice');
    }

    public function latestPaymentProof(): HasOne
    {
        return $this->hasOne(PaymentProof::class, 'id_invoice', 'id_invoice')->latestOfMany('id_payment_proof');
    }

    public function isVerified(): bool
    {
        return $this->status_pembayaran === 'Terverifikasi';
    }
}

