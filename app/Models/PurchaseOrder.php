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
        'iva_included',
        'rms',
        'rf',
        'contact_email',
        'delivery_location',
        'receiver',
        'receiver_phone',
        'pdf_path',
        'xml_path',
        'invoice_pdf_path',
        'invoice_xml_path',
        'payment_complement_path',
        'payment_validated_at',
        'payment_validated_by',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'amount' => 'decimal:2',
            'iva_percentage' => 'decimal:2',
            'iva_included' => 'boolean',
            'payment_validated_at' => 'datetime',
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

    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(UploadedDocument::class);
    }

    public function hasDocument(string $kind): bool
    {
        $legacyPath = match ($kind) {
            'purchase_order_pdf' => $this->pdf_path,
            'purchase_order_xml' => $this->xml_path,
            'invoice_pdf' => $this->invoice_pdf_path,
            'invoice_xml' => $this->invoice_xml_path,
            'payment_complement' => $this->payment_complement_path,
            default => null,
        };

        if ($legacyPath !== null) {
            return true;
        }

        if ($this->relationLoaded('uploadedDocuments')) {
            return $this->uploadedDocuments->contains('kind', $kind);
        }

        return $this->uploadedDocuments()->where('kind', $kind)->exists();
    }
}
