<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address_id',
        'order_number',
        'order_type',
        'payment_method',
        'payment_status',
        'order_status',
        'subtotal',
        'delivery_fee',
        'total',
        'special_instruction',
    ];

    protected function casts(): array
    {
        return [
            'order_type'     => OrderType::class,
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
            'order_status'   => OrderStatus::class,
            'subtotal'       => 'decimal:2',
            'delivery_fee'   => 'decimal:2',
            'total'          => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Generate a unique human-readable order number: BB100001, BB100002, ...
     */
    public static function generateOrderNumber(): string
    {
        $last = static::orderByDesc('id')->value('id') ?? 0;
        $sequence = $last + 1;
        return 'BB' . str_pad(100000 + $sequence, 6, '0', STR_PAD_LEFT);
    }

    public function scopeForCustomer($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
