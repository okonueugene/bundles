@extends('layouts.app')

@section('title', 'Order '.$transaction->order_reference.' — Admin')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
    @include('admin.partials.nav', [
        'title' => $transaction->order_reference ?: 'Transaction #'.$transaction->id,
        'subtitle' => 'Full transaction record',
        'current' => 'orders',
    ])

    @if (session('success'))
        <p class="mb-4 text-sm text-green-700">{{ session('success') }}</p>
    @endif

    <div class="mb-4">
        <a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-ok hover:text-ok-dark">← Back to orders</a>
    </div>

    <div class="bg-white rounded-xl border border-okoa-border overflow-hidden">
        <dl class="divide-y divide-okoa-border text-sm">
            @php
                $fields = [
                    'id' => $transaction->id,
                    'order_reference' => $transaction->order_reference,
                    'checkout_request_id' => $transaction->checkout_request_id,
                    'mpesa_receipt_number' => $transaction->mpesa_receipt_number,
                    'phone_number' => $transaction->phone_number,
                    'amount' => $transaction->amount,
                    'package_code' => $transaction->package_code,
                    'is_substituted' => $transaction->is_substituted ? 'yes' : 'no',
                    'original_package_code' => $transaction->original_package_code,
                    'status' => $transaction->status,
                    'provider_used' => $transaction->provider_used,
                    'last_attempted_provider' => $transaction->last_attempted_provider,
                    'claimed_by' => $transaction->claimed_by,
                    'attempt_count' => $transaction->attempt_count,
                    'background_attempt_count' => $transaction->background_attempt_count,
                    'alert_sent_at' => $transaction->alert_sent_at,
                    'alert_channel' => $transaction->alert_channel,
                    'manually_resolved_at' => $transaction->manually_resolved_at,
                    'resolution_note' => $transaction->resolution_note,
                    'client_sms_sent_at' => $transaction->client_sms_sent_at,
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at,
                ];
            @endphp
            @foreach ($fields as $label => $value)
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 px-4 py-3">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-okoa-muted">{{ str_replace('_', ' ', $label) }}</dt>
                    <dd class="sm:col-span-2 font-mono text-okoa-charcoal break-all">{{ $value === null || $value === '' ? '—' : $value }}</dd>
                </div>
            @endforeach
            <div class="px-4 py-3">
                <dt class="text-xs font-semibold uppercase tracking-wider text-okoa-muted mb-2">raw payload</dt>
                <dd>
                    <pre class="overflow-x-auto rounded-lg bg-okoa-bg border border-okoa-border p-3 text-xs font-mono text-okoa-charcoal whitespace-pre-wrap">{{ $transaction->raw_payload ? json_encode($transaction->raw_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '—' }}</pre>
                </dd>
            </div>
        </dl>
    </div>

    @if ($transaction->status === 'needs_attention')
        <div class="mt-6 bg-white rounded-xl border border-okoa-border p-4">
            <h2 class="text-lg font-bold text-okoa-charcoal">Manual resolution</h2>
            <p class="mt-1 text-sm text-okoa-muted">Record how this order was handled. This does not re-run fulfillment.</p>

            <form method="POST" action="{{ route('admin.orders.resolve', $transaction) }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label for="resolution_status" class="block text-sm font-medium">New status</label>
                    <select name="resolution_status" id="resolution_status" required
                            class="mt-2 w-full rounded-lg border border-okoa-border bg-white px-3 py-2 text-sm focus:border-ok focus:outline-none focus:ring-1 focus:ring-ok">
                        <option value="fulfilled" @selected(old('resolution_status') === 'fulfilled')>fulfilled</option>
                        <option value="needs_attention" @selected(old('resolution_status', 'needs_attention') === 'needs_attention')>needs_attention</option>
                    </select>
                    @error('resolution_status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="resolution_note" class="block text-sm font-medium">Resolution note</label>
                    <textarea name="resolution_note" id="resolution_note" rows="4" required maxlength="1000"
                              class="mt-2 w-full rounded-lg border border-okoa-border bg-white px-3 py-2 text-sm focus:border-ok focus:outline-none focus:ring-1 focus:ring-ok">{{ old('resolution_note') }}</textarea>
                    @error('resolution_note')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="min-h-11 rounded-lg bg-ok px-4 py-2 text-sm font-semibold text-white hover:bg-ok-dark">
                    Save resolution
                </button>
            </form>
        </div>
    @endif
</div>
@endsection
