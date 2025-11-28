<?php

namespace App\Enums;

use App\Traits\HasArrayValues;

enum ProductStatus: string
{
    use HasArrayValues;

    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
}
