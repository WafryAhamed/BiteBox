<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'PENDING';
    case CONFIRMED = 'CONFIRMED';
    case PREPARING = 'PREPARING';
    case READY = 'READY';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';

    /**
     * Returns valid next statuses for admin transitions.
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PENDING    => [self::CONFIRMED, self::CANCELLED],
            self::CONFIRMED  => [self::PREPARING, self::CANCELLED],
            self::PREPARING  => [self::READY],
            self::READY      => [self::COMPLETED],
            self::COMPLETED  => [],
            self::CANCELLED  => [],
        };
    }

    public function canCustomerCancel(): bool
    {
        return $this === self::PENDING;
    }

    public function label(): string
    {
        return match ($this) {
            self::PENDING   => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::PREPARING => 'Preparing',
            self::READY     => 'Ready',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }
}
