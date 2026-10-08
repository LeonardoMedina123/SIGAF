<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'folio',
        'order_date',
        'requisition_id',
        'supplier_id',
        'amount',
        'partida',
        'iva_percentage',
        'rms',
        'rf',
        'contact_email',
        'delivery_location',
        'receiver',
        'receiver_phone',
        'pdf_path',
        'xml_path',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'amount' => 'decimal:2',
            'iva_percentage' => 'decimal:2',
        ];
    }

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Requisition::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function paymentComplements(): HasMany
    {
        return $this->hasMany(PaymentComplement::class);
    }
}
