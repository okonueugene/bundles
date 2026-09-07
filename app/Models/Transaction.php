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
        'background_attempt_count',
        'alert_sent_at',
        'alert_channel',
        'raw_payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'attempt_count' => 'integer',
        'background_attempt_count' => 'integer',
        'alert_sent_at' => 'datetime',
        'raw_payload' => 'array',
    ];
}
