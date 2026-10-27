<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Requisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'folio',
        'created_on',
        'requested_on',
        'amount',
        'partida',
        'sp',
        'ppp',
        'sup',
        'dir',
        'process',
        'rf',
        'pdf_path',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'created_on' => 'date',
            'requested_on' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function purchaseOrder(): HasOne
    {
        return $this->hasOne(PurchaseOrder::class);
    }

    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(UploadedDocument::class);
    }
}
