@extends('layouts.app')

@section('title', 'Admin Orders - Okoa')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
    @include('admin.partials.nav', [
        'title' => 'Admin Back Office',
        'subtitle' => 'Manage payments, order fulfillment, and system logs.',
        'current' => 'orders',
    ])

    <!-- Filter and Search Bar -->
    <div class="bg-white rounded-xl border border-okoa-border p-4 mb-6">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="md:col-span-2">
                <label for="search" class="block text-xs font-semibold uppercase tracking-wider text-okoa-muted mb-1.5">Search Order</label>
                <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Search Ref, Phone, or M-PESA Receipt..." 
                       class="w-full bg-okoa-bg text-sm rounded-lg border border-okoa-border px-3 py-2 text-okoa-charcoal placeholder-okoa-muted focus:outline-none focus:ring-1 focus:ring-ok focus:border-ok">
            </div>
            <div>
                <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-okoa-muted mb-1.5">Status Filter</label>
                <select name="status" id="status" 
                        class="w-full bg-okoa-bg text-sm rounded-lg border border-okoa-border px-3 py-2 text-okoa-charcoal focus:outline-none focus:ring-1 focus:ring-ok focus:border-ok">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="fulfilled" {{ $statusFilter === 'fulfilled' ? 'selected' : '' }}>Fulfilled</option>
                    <option value="queued_for_retry" {{ $statusFilter === 'queued_for_retry' ? 'selected' : '' }}>Queued for Retry</option>
                    <option value="processing" {{ $statusFilter === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="needs_attention" {{ $statusFilter === 'needs_attention' ? 'selected' : '' }}>Needs Attention</option>
                    <option value="over_fulfillment_flagged" {{ $statusFilter === 'over_fulfillment_flagged' ? 'selected' : '' }}>Over Fulfillment</option>
                    <option value="payment_failed" {{ $statusFilter === 'payment_failed' ? 'selected' : '' }}>Payment Failed</option>
                </select>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="flex-1 bg-ok hover:bg-ok-dark text-white text-sm font-semibold px-4 py-2 rounded-lg transition shadow-sm">
                    Filter
                </button>
                <a href="{{ route('admin.orders.index') }}" class="bg-okoa-bg border border-okoa-border text-okoa-muted hover:text-okoa-charcoal text-sm font-semibold px-4 py-2 rounded-lg transition flex items-center justify-center">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-xl border border-okoa-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-okoa-bg border-b border-okoa-border text-xs font-semibold uppercase tracking-wider text-okoa-muted">
                        <th class="px-4 py-3">Reference / Phone</th>
                        <th class="px-4 py-3">M-PESA Receipt</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3">Package Code</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-okoa-border text-sm">
                    @forelse($transactions as $tx)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3.5">
                                <div class="font-medium text-okoa-charcoal">
                                    {{ $tx->order_reference ?: 'C2B (Till/Paybill)' }}
                                </div>
                                <div class="text-xs text-okoa-muted">
                                    {{ $tx->phone_number }}
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-okoa-charcoal font-mono text-xs">
                                {{ $tx->mpesa_receipt_number ?: '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-semibold text-okoa-charcoal">
                                KSh {{ number_format($tx->amount, 2) }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-xs text-okoa-muted">
                                {{ $tx->package_code ?: 'Unmapped' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium 
                                    @if($tx->status === 'fulfilled') bg-green-50 text-green-700 border border-green-200
                                    @elseif($tx->status === 'needs_attention') bg-red-50 text-red-700 border border-red-200
                                    @elseif($tx->status === 'pending' || $tx->status === 'processing') bg-blue-50 text-blue-700 border border-blue-200
                                    @elseif($tx->status === 'queued_for_retry') bg-yellow-50 text-yellow-700 border border-yellow-200
                                    @else bg-slate-50 text-slate-700 border border-slate-200 @endif">
                                    {{ str_replace('_', ' ', $tx->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-xs text-okoa-muted">
                                {{ $tx->created_at->format('M j, H:i') }}
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <a href="{{ route('admin.orders.show', $tx) }}" class="text-xs font-semibold text-ok hover:text-ok-dark">
                                    View Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-okoa-muted">
                                No transactions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="px-4 py-3 border-t border-okoa-border bg-okoa-bg">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
