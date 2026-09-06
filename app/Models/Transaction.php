<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'mpesa_receipt_number',
        'phone_number',
        'amount',
        'package_code',
        'status',
        'provider_used',
        'claimed_by',
        'attempt_count',
        'raw_payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'attempt_count' => 'integer',
        'raw_payload' => 'array',
    ];
}
