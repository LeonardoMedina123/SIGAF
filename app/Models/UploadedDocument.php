<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;

class UploadedDocument extends Model
{
    protected $fillable = [
        'requisition_id',
        'purchase_order_id',
        'kind',
        'original_name',
        'mime_type',
        'content',
    ];

    protected $hidden = [
        'content',
    ];

    public static function replaceFor(
        Requisition|PurchaseOrder $owner,
        string $kind,
        UploadedFile $file,
    ): self {
        return $owner->uploadedDocuments()->updateOrCreate(
            ['kind' => $kind],
            [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                'content' => $file->getContent(),
            ],
        );
    }

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Requisition::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
