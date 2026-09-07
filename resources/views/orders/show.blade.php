@extends('layouts.app')

@section('title', 'Order status — Okoa')

@section('content')
<div class="max-w-md mx-auto px-4 sm:px-6 py-8">
    <div class="rounded-xl border border-okoa-border bg-white p-6 shadow-sm">
        <h1 class="text-lg font-bold">{{ $status['title'] }}</h1>
        @if ($status['subtitle'])
            <p class="mt-1 text-sm font-medium text-ok">{{ $status['subtitle'] }}</p>
        @endif
        <p class="mt-3 text-sm text-okoa-muted">{{ $status['message'] }}</p>

        @if ($transaction->order_reference)
            <p class="mt-4 text-xs text-okoa-muted">
                Order reference: <span class="font-medium text-okoa-charcoal">{{ $transaction->order_reference }}</span>
            </p>
        @endif

        <div class="mt-6">
            <a href="{{ route('catalog.index') }}" class="inline-flex min-h-11 items-center rounded-lg bg-ok px-4 py-2 text-sm font-semibold text-white hover:bg-ok-dark">
                Buy another bundle
            </a>
        </div>
    </div>
</div>
@endsection
