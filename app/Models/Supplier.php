<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'name',
        'rfc',
        'curp',
        'address',
        'city',
        'phone',
        'representative',
        'cellphone',
        'economic_activity',
        'iva_percentage',
        'status',
        'it_capture',
    ];

    protected function casts(): array
    {
        return ['iva_percentage' => 'decimal:2'];
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
