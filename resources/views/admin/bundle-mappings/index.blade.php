@extends('layouts.app')

@section('title', 'Bundle mappings — Admin')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
    @include('admin.partials.nav', [
        'title' => 'Bundle mappings',
        'subtitle' => 'Catalog rows used to map M-PESA amounts to package codes',
        'current' => 'bundles',
    ])

    @if (session('success'))
        <p class="mb-4 text-sm text-green-700">{{ session('success') }}</p>
    @endif

    <div class="mb-4 flex justify-end">
        <a href="{{ route('admin.bundle-mappings.create') }}" class="inline-flex min-h-11 items-center rounded-lg bg-ok px-4 py-2 text-sm font-semibold text-white hover:bg-ok-dark">
            New Bundle
        </a>
    </div>

    <div class="bg-white rounded-xl border border-okoa-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-okoa-bg border-b border-okoa-border text-xs font-semibold uppercase tracking-wider text-okoa-muted">
                        <th class="px-4 py-3">Network</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Slug</th>
                        <th class="px-4 py-3">Package</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Available</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-okoa-border text-sm">
                    @forelse ($mappings as $mapping)
                        <tr>
                            <td class="px-4 py-3.5 text-okoa-charcoal">{{ $mapping->network }}</td>
                            <td class="px-4 py-3.5 font-semibold">KSh {{ number_format($mapping->amount, 2) }}</td>
                            <td class="px-4 py-3.5 font-mono text-xs">{{ $mapping->slug }}</td>
                            <td class="px-4 py-3.5 font-mono text-xs text-okoa-muted">{{ $mapping->package_code }}</td>
                            <td class="px-4 py-3.5">{{ $mapping->type }}</td>
                            <td class="px-4 py-3.5 text-xs">{{ $mapping->is_available ? 'yes' : 'no' }}</td>
                            <td class="px-4 py-3.5 text-right space-x-3">
                                <a href="{{ route('admin.bundle-mappings.edit', $mapping) }}" class="text-xs font-semibold text-ok hover:text-ok-dark">Edit</a>
                                <form method="POST" action="{{ route('admin.bundle-mappings.destroy', $mapping) }}" class="inline" onsubmit="return confirm('Delete this bundle mapping?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-okoa-muted">No bundle mappings yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
