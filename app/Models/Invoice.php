<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $primaryKey = 'folio';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'folio',
        'supplier_id',
        'uuid',
        'issuer_tax_regime',
        'receiver_tax_regime',
        'payment_form',
        'payment_method',
        'cfdi_use',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_folio', 'folio');
    }

    public function paymentComplements(): HasMany
    {
        return $this->hasMany(PaymentComplement::class, 'invoice_folio', 'folio');
    }
}
