<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemReimbursementDocument extends Model
{
    protected $fillable = [
        'order_number',
        'original_name',
        'mime_type',
        'content',
        'editing_enabled',
    ];

    protected $hidden = [
        'content',
    ];

    protected function casts(): array
    {
        return [
            'order_number' => 'integer',
            'editing_enabled' => 'boolean',
        ];
    }
}
