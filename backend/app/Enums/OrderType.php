<?php

namespace App\Enums;

enum OrderType: string
{
    case PICKUP   = 'PICKUP';
    case DELIVERY = 'DELIVERY';
}
