@extends('layouts.app')

@section('title', ($mapping->exists ? 'Edit bundle' : 'New bundle').' — Admin')

@section('content')
@php
    $from = old('available_from', $mapping->available_from);
    $until = old('available_until', $mapping->available_until);
@endphp
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
    @include('admin.partials.nav', [
        'title' => $mapping->exists ? 'Edit bundle' : 'New bundle',
        'subtitle' => $mapping->exists ? $mapping->slug : 'Create a catalog mapping',
        'current' => 'bundles',
    ])

    <div class="mb-4">
        <a href="{{ route('admin.bundle-mappings.index') }}" class="text-sm font-medium text-ok hover:text-ok-dark">← Back to bundles</a>
    </div>

    <div class="bg-white rounded-xl border border-okoa-border p-4 sm:p-6">
        <form method="POST"
              action="{{ $mapping->exists ? route('admin.bundle-mappings.update', $mapping) : route('admin.bundle-mappings.store') }}"
              class="space-y-4">
            @csrf
            @if ($mapping->exists)
                @method('PUT')
            @endif

            <div>
                <label for="network" class="block text-sm font-medium">Network</label>
                <select name="network" id="network" required
                        class="mt-2 w-full rounded-lg border border-okoa-border bg-white px-3 py-2 text-sm focus:border-ok focus:outline-none focus:ring-1 focus:ring-ok">
                    <option value="safaricom" @selected(old('network', $mapping->network ?: 'safaricom') === 'safaricom')>safaricom</option>
                </select>
                @error('network') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="amount" class="block text-sm font-medium">Amount</label>
                <input type="number" name="amount" id="amount" min="1" step="0.01" required
                       value="{{ old('amount', $mapping->amount) }}"
                       class="mt-2 w-full rounded-lg border border-okoa-border bg-white px-3 py-2 text-sm focus:border-ok focus:outline-none focus:ring-1 focus:ring-ok">
                @error('amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="package_code" class="block text-sm font-medium">Package code</label>
                <input type="text" name="package_code" id="package_code" maxlength="64" required
                       value="{{ old('package_code', $mapping->package_code) }}"
                       class="mt-2 w-full rounded-lg border border-okoa-border bg-white px-3 py-2 text-sm focus:border-ok focus:outline-none focus:ring-1 focus:ring-ok">
                @error('package_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="slug" class="block text-sm font-medium">Slug</label>
                <input type="text" name="slug" id="slug" maxlength="64" required
                       value="{{ old('slug', $mapping->slug) }}"
                       class="mt-2 w-full rounded-lg border border-okoa-border bg-white px-3 py-2 text-sm focus:border-ok focus:outline-none focus:ring-1 focus:ring-ok">
                @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium">Description</label>
                <input type="text" name="description" id="description" maxlength="255" required
                       value="{{ old('description', $mapping->description) }}"
                       class="mt-2 w-full rounded-lg border border-okoa-border bg-white px-3 py-2 text-sm focus:border-ok focus:outline-none focus:ring-1 focus:ring-ok">
                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="type" class="block text-sm font-medium">Type</label>
                <select name="type" id="type" required
                        class="mt-2 w-full rounded-lg border border-okoa-border bg-white px-3 py-2 text-sm focus:border-ok focus:outline-none focus:ring-1 focus:ring-ok">
                    @foreach (['data', 'sms', 'minutes'] as $type)
                        <option value="{{ $type }}" @selected(old('type', $mapping->type ?: 'data') === $type)>{{ $type }}</option>
                    @endforeach
                </select>
                @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="validity" class="block text-sm font-medium">Validity</label>
                <input type="text" name="validity" id="validity" maxlength="64"
                       value="{{ old('validity', $mapping->validity) }}"
                       class="mt-2 w-full rounded-lg border border-okoa-border bg-white px-3 py-2 text-sm focus:border-ok focus:outline-none focus:ring-1 focus:ring-ok">
                @error('validity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="available_from" class="block text-sm font-medium">Available from</label>
                    <input type="time" name="available_from" id="available_from" step="1"
                           value="{{ $from }}"
                           class="mt-2 w-full rounded-lg border border-okoa-border bg-white px-3 py-2 text-sm focus:border-ok focus:outline-none focus:ring-1 focus:ring-ok">
                    @error('available_from') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="available_until" class="block text-sm font-medium">Available until</label>
                    <input type="time" name="available_until" id="available_until" step="1"
                           value="{{ $until }}"
                           class="mt-2 w-full rounded-lg border border-okoa-border bg-white px-3 py-2 text-sm focus:border-ok focus:outline-none focus:ring-1 focus:ring-ok">
                    @error('available_until') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="fallback_package_code" class="block text-sm font-medium">Fallback package code</label>
                <input type="text" name="fallback_package_code" id="fallback_package_code" maxlength="64"
                       value="{{ old('fallback_package_code', $mapping->fallback_package_code) }}"
                       class="mt-2 w-full rounded-lg border border-okoa-border bg-white px-3 py-2 text-sm focus:border-ok focus:outline-none focus:ring-1 focus:ring-ok">
                @error('fallback_package_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            @if ($mapping->exists)
                <p class="text-sm text-okoa-muted">
                    Current availability (computed from the time window, not stored):
                    <span class="font-medium text-okoa-charcoal">{{ $mapping->is_available ? 'available' : 'restricted' }}</span>
                </p>
            @endif

            <button type="submit" class="min-h-11 rounded-lg bg-ok px-4 py-2 text-sm font-semibold text-white hover:bg-ok-dark">
                {{ $mapping->exists ? 'Update bundle' : 'Create bundle' }}
            </button>
        </form>
    </div>
</div>
@endsection
