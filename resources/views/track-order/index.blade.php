@extends('layouts.app')

@section('title', 'Check Order — Okoa')

@section('content')
<div class="max-w-md mx-auto px-4 sm:px-6 py-8">
    <div class="rounded-xl border border-okoa-border bg-white p-6 shadow-sm">
        <h1 class="text-lg font-bold">Check your order</h1>
        <p class="mt-1 text-sm text-okoa-muted">Enter your order reference to see the latest status.</p>

        <form method="POST" action="{{ route('track.lookup') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="order_reference" class="block text-sm font-medium">Order reference</label>
                <input
                    type="text"
                    id="order_reference"
                    name="order_reference"
                    value="{{ old('order_reference') }}"
                    placeholder="e.g. OKOA-A1B2C3D4"
                    class="mt-2 block w-full rounded-lg border border-okoa-border bg-white px-4 py-3 text-sm shadow-sm focus:border-ok focus:outline-none focus:ring-1 focus:ring-ok uppercase"
                    required
                >
            </div>

            @error('order_reference')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            @if (session('error'))
                <p class="text-sm text-red-600">{{ session('error') }}</p>
            @endif

            <button type="submit" class="w-full min-h-12 rounded-lg bg-ok px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-ok-dark transition">
                Track order
            </button>
        </form>
    </div>
</div>
@endsection
