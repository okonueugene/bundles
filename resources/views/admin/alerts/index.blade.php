@extends('layouts.app')

@section('title', 'Alert history — Admin')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
    @include('admin.partials.nav', [
        'title' => 'Alert history',
        'subtitle' => 'Transactions that triggered an outbound admin alert',
        'current' => 'alerts',
    ])

    <div class="bg-white rounded-xl border border-okoa-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-okoa-bg border-b border-okoa-border text-xs font-semibold uppercase tracking-wider text-okoa-muted">
                        <th class="px-4 py-3">Reference</th>
                        <th class="px-4 py-3">Alert channel</th>
                        <th class="px-4 py-3">Alert sent at</th>
                        <th class="px-4 py-3">Current status</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-okoa-border text-sm">
                    @forelse ($transactions as $tx)
                        <tr>
                            <td class="px-4 py-3.5 font-medium text-okoa-charcoal">
                                {{ $tx->order_reference ?: 'C2B #'.$tx->id }}
                            </td>
                            <td class="px-4 py-3.5 text-okoa-muted">{{ $tx->alert_channel ?: '—' }}</td>
                            <td class="px-4 py-3.5 text-xs text-okoa-muted">{{ $tx->alert_sent_at?->format('M j, Y H:i') }}</td>
                            <td class="px-4 py-3.5">{{ str_replace('_', ' ', $tx->status) }}</td>
                            <td class="px-4 py-3.5 text-right">
                                <a href="{{ route('admin.orders.show', $tx) }}" class="text-xs font-semibold text-ok hover:text-ok-dark">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-okoa-muted">No alerts recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($transactions->hasPages())
            <div class="px-4 py-3 border-t border-okoa-border bg-okoa-bg">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
