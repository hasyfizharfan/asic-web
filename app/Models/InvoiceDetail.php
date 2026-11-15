<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceDetail extends Model
{
    use HasFactory;

    protected $table = 'invoice_detail';
    protected $primaryKey = 'id_invoice_detail';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = ['id_invoice','jenis_biaya','deskripsi','jumlah','harga_satuan','subtotal'];

    protected function casts(): array
    {
        return ['jumlah' => 'integer', 'harga_satuan' => 'decimal:2', 'subtotal' => 'decimal:2'];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'id_invoice', 'id_invoice');
    }
}
