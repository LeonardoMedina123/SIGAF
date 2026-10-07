<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Requisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'folio',
        'created_on',
        'requested_on',
        'needed_by',
        'amount',
        'partida',
        'sp',
        'ppp',
        'sup',
        'dir',
        'process',
        'rf',
        'budget_key',
        'quantity',
        'unit',
        'unit_cost',
        'description',
        'department',
        'justification',
        'budget_confirmed',
        'pdf_path',
        'xml_path',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'created_on' => 'date',
            'requested_on' => 'date',
            'needed_by' => 'date',
            'amount' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'budget_confirmed' => 'boolean',
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
}
