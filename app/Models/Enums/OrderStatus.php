<?php

namespace App\Models\Enums;

enum OrderStatus: string
{
    case PENDING_STATUS = 'pending';
    case PAID_STATUS = 'paid';
}
