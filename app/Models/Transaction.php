<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'order_reference',
        'checkout_request_id',
        'mpesa_receipt_number',
        'phone_number',
        'amount',
        'package_code',
        'is_substituted',
        'original_package_code',
        'status',
        'provider_used',
        'last_attempted_provider',
        'claimed_by',
        'attempt_count',
        'background_attempt_count',
        'alert_sent_at',
        'alert_channel',
        'manually_resolved_at',
        'resolution_note',
        'client_sms_sent_at',
        'raw_payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'attempt_count' => 'integer',
        'background_attempt_count' => 'integer',
        'is_substituted' => 'boolean',
        'alert_sent_at' => 'datetime',
        'manually_resolved_at' => 'datetime',
        'client_sms_sent_at' => 'datetime',
        'raw_payload' => 'array',
    ];
}
