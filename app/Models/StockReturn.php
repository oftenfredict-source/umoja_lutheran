<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'product_variant_id',
        'quantity',
        'unit',
        'status',
        'received_by',
        'received_at',
        'notes',
        'rejection_reason',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'received_at' => 'datetime',
    ];

    /**
     * Get the staff member who returned this
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    /**
     * Get the product variant being returned
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Get the storekeeper who received this
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'received_by');
    }

    /**
     * Helper to get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending Receipt',
            'received' => 'Received',
            'rejected' => 'Rejected',
            default => ucfirst($this->status),
        };
    }

    /**
     * Helper to get status color class
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'received' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }
}
