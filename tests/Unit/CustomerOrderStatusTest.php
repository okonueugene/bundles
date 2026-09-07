<?php

namespace Tests\Unit;

use App\Models\BundleMapping;
use App\Models\Transaction;
use App\Support\CustomerOrderStatus;
use Tests\TestCase;

class CustomerOrderStatusTest extends TestCase
{
    public function test_paid_is_distinct_from_fulfilled(): void
    {
        $product = new BundleMapping(['description' => '1 GB']);
        $paid = new Transaction([
            'status' => 'pending',
            'mpesa_receipt_number' => 'ABC123',
            'phone_number' => '254712345678',
        ]);
        $fulfilled = new Transaction([
            'status' => 'fulfilled',
            'mpesa_receipt_number' => 'ABC123',
            'phone_number' => '254712345678',
        ]);

        $paidStatus = CustomerOrderStatus::for($paid, $product);
        $fulfilledStatus = CustomerOrderStatus::for($fulfilled, $product);

        $this->assertSame('paid', $paidStatus['key']);
        $this->assertSame('Bundle being delivered', $paidStatus['subtitle']);
        $this->assertSame('fulfilled', $fulfilledStatus['key']);
        $this->assertSame('Bundle delivered', $fulfilledStatus['subtitle']);
        $this->assertStringContainsString('1 GB', $fulfilledStatus['message']);
        $this->assertStringContainsString('0712345678', $fulfilledStatus['message']);
    }

    public function test_delayed_fulfillment_uses_friendly_copy(): void
    {
        $status = CustomerOrderStatus::for(new Transaction([
            'status' => 'queued_for_retry',
            'phone_number' => '254712345678',
        ]));

        $this->assertSame('fulfillment_pending', $status['key']);
        $this->assertSame('Delivery is taking longer than expected', $status['subtitle']);
        $this->assertStringContainsString('payment is safe', $status['message']);
    }

    public function test_needs_attention_does_not_expose_internal_errors(): void
    {
        $status = CustomerOrderStatus::for(new Transaction([
            'status' => 'needs_attention',
            'phone_number' => '254712345678',
            'raw_payload' => ['error' => 'PROVIDER_TIMEOUT'],
        ]));

        $this->assertSame('needs_attention', $status['key']);
        $this->assertStringContainsString('reviewing your order', $status['title']);
        $this->assertStringNotContainsString('PROVIDER_TIMEOUT', json_encode($status));
    }
}
