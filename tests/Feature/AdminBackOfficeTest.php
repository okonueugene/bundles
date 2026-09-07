<?php

namespace Tests\Feature;

use App\Models\BundleMapping;
use App\Models\Transaction;
use Tests\TestCase;

class AdminBackOfficeTest extends TestCase
{
    public function test_admin_orders_list_filter_and_search(): void
    {
        $suffix = now()->format('YmdHisv');
        $pending = Transaction::create([
            'order_reference' => "OKOA-ADM{$suffix}-P",
            'mpesa_receipt_number' => "ADMP{$suffix}",
            'phone_number' => '254700'.random_int(100000, 999999),
            'amount' => 99,
            'package_code' => 'DATA_1GB',
            'status' => 'pending',
        ]);
        $attention = Transaction::create([
            'order_reference' => "OKOA-ADM{$suffix}-A",
            'mpesa_receipt_number' => "ADMA{$suffix}",
            'phone_number' => '254711'.random_int(100000, 999999),
            'amount' => 199,
            'package_code' => 'DATA_2GB',
            'status' => 'needs_attention',
        ]);

        $this->get('/admin/orders')
            ->assertOk()
            ->assertSee($pending->order_reference)
            ->assertSee($attention->order_reference);

        $this->get('/admin/orders?status=needs_attention')
            ->assertOk()
            ->assertSee($attention->order_reference)
            ->assertDontSee($pending->order_reference);

        $this->get('/admin/orders?search='.$pending->mpesa_receipt_number)
            ->assertOk()
            ->assertSee($pending->order_reference)
            ->assertDontSee($attention->order_reference);
    }

    public function test_admin_can_resolve_needs_attention_transaction(): void
    {
        $suffix = now()->format('YmdHisv');
        $transaction = Transaction::create([
            'order_reference' => "OKOA-RES{$suffix}",
            'mpesa_receipt_number' => "RES{$suffix}",
            'phone_number' => '254722'.random_int(100000, 999999),
            'amount' => 99,
            'package_code' => 'DATA_1GB',
            'status' => 'needs_attention',
            'attempt_count' => 3,
            'background_attempt_count' => 2,
            'provider_used' => null,
            'last_attempted_provider' => 'provider.primary',
            'raw_payload' => ['ResultCode' => 0],
        ]);

        $this->get(route('admin.orders.show', $transaction))
            ->assertOk()
            ->assertSee('Manual resolution')
            ->assertSee('last attempted provider')
            ->assertSee('provider.primary');

        $this->post(route('admin.orders.resolve', $transaction), [
            'resolution_status' => 'fulfilled',
            'resolution_note' => 'Confirmed delivered via Safaricom SMS after provider timeout.',
        ])->assertRedirect(route('admin.orders.show', $transaction));

        $transaction->refresh();

        $this->assertSame('fulfilled', $transaction->status);
        $this->assertNotNull($transaction->manually_resolved_at);
        $this->assertSame('Confirmed delivered via Safaricom SMS after provider timeout.', $transaction->resolution_note);
    }

    public function test_admin_alerts_only_include_alerted_transactions(): void
    {
        $suffix = now()->format('YmdHisv');
        $alerted = Transaction::create([
            'order_reference' => "OKOA-ALR{$suffix}",
            'mpesa_receipt_number' => "ALR{$suffix}",
            'phone_number' => '254733'.random_int(100000, 999999),
            'amount' => 99,
            'status' => 'needs_attention',
            'alert_sent_at' => now(),
            'alert_channel' => 'telegram',
        ]);
        $silent = Transaction::create([
            'order_reference' => "OKOA-SIL{$suffix}",
            'mpesa_receipt_number' => "SIL{$suffix}",
            'phone_number' => '254744'.random_int(100000, 999999),
            'amount' => 99,
            'status' => 'pending',
        ]);

        $this->get('/admin/alerts')
            ->assertOk()
            ->assertSee($alerted->order_reference)
            ->assertDontSee($silent->order_reference);
    }

    public function test_bundle_mapping_crud(): void
    {
        $slug = 'admin-test-'.now()->format('YmdHisv');
        $amount = 8888 + (random_int(10, 99) / 100);

        $this->post(route('admin.bundle-mappings.store'), [
            'network' => 'safaricom',
            'amount' => $amount,
            'package_code' => 'TEST_ADMIN_PKG',
            'slug' => $slug,
            'description' => 'Admin test bundle',
            'type' => 'data',
            'validity' => '1 hour',
            'available_from' => '08:00:00',
            'available_until' => '22:00:00',
            'fallback_package_code' => 'TEST_FALLBACK',
        ])->assertRedirect(route('admin.bundle-mappings.index'));

        $mapping = BundleMapping::where('slug', $slug)->first();
        $this->assertNotNull($mapping);
        $this->assertSame('TEST_ADMIN_PKG', $mapping->package_code);
        $this->assertEquals($amount, (float) $mapping->amount);

        $this->put(route('admin.bundle-mappings.update', $mapping), [
            'network' => 'safaricom',
            'amount' => $amount,
            'package_code' => 'TEST_ADMIN_PKG_EDIT',
            'slug' => $slug,
            'description' => 'Admin test bundle edited',
            'type' => 'sms',
            'validity' => '2 hours',
            'available_from' => '09:00:00',
            'available_until' => '21:00:00',
            'fallback_package_code' => 'TEST_FALLBACK_2',
        ])->assertRedirect(route('admin.bundle-mappings.index'));

        $mapping->refresh();
        $this->assertSame('TEST_ADMIN_PKG_EDIT', $mapping->package_code);
        $this->assertSame('Admin test bundle edited', $mapping->description);
        $this->assertSame('sms', $mapping->type);

        $this->delete(route('admin.bundle-mappings.destroy', $mapping))
            ->assertRedirect(route('admin.bundle-mappings.index'));

        $this->assertNull(BundleMapping::where('slug', $slug)->first());
    }

    public function test_track_order_lookup_is_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/track-order', ['order_reference' => 'OKOA-MISSING'])->assertStatus(302);
        }

        $this->post('/track-order', ['order_reference' => 'OKOA-MISSING'])->assertStatus(429);
    }

    public function test_public_order_show_is_rate_limited(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->get('/orders/OKOA-MISSING-REF')->assertStatus(404);
        }

        $this->get('/orders/OKOA-MISSING-REF')->assertStatus(429);
    }
}
