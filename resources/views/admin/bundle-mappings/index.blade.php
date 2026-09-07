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
    @endsection

    <div class="bg-white rounded-xl border border-okoa-border overflow-hidden">
        @forelse($mappings as $mapping)
            {{-- Mobile card, shown only below sm --}}
            <div class="sm:hidden border-b border-okoa-border p-4 last:border-b-0">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="font-medium text-okoa-charcoal">{{ $mapping->description }}</div>
                        <div class="text-xs font-mono text-okoa-muted">{{ $mapping->slug }}</div>
                    </div>
                    <span class="text-xs font-semibold text-okoa-charcoal">KSh {{ number_format($mapping->amount, 2) }}</span>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <div class="text-xs text-okoa-muted">Network</div>
                        <div class="text-okoa-charcoal">{{ $mapping->network }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-okoa-muted">Type</div>
                        <div class="text-okoa-charcoal">{{ $mapping->type }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-okoa-muted">Package</div>
                        <div class="font-mono text-xs text-okoa-muted break-all">{{ $mapping->package_code }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-okoa-muted">Available</div>
                        <div class="text-okoa-charcoal">{{ $mapping->is_available ? 'yes' : 'no' }}</div>
                    </div>
                </div>
                <div class="mt-3 inline-flex items-center gap-2">
                    <a href="{{ route('admin.bundle-mappings.edit', $mapping) }}" class="inline-flex min-h-9 items-center rounded-md px-2.5 py-1 text-xs font-semibold text-ok hover:bg-ok-light transition">Edit</a>
                    <form method="POST" action="{{ route('admin.bundle-mappings.destroy', $mapping) }}" class="inline" onsubmit="return confirm('Delete this bundle mapping?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex min-h-9 items-center rounded-md px-2.5 py-1 text-xs font-semibold text-red-600 hover:bg-red-50 transition">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-okoa-muted sm:col-span-2">No bundle mappings yet.</div>
        @endforelse

        <div class="hidden sm:block overflow-x-auto">
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
                    @forelse($mappings as $mapping)
                        <tr>
                            <td class="px-4 py-3.5 text-okoa-charcoal">{{ $mapping->network }}</td>
                            <td class="px-4 py-3.5 font-semibold">KSh {{ number_format($mapping->amount, 2) }}</td>
                            <td class="px-4 py-3.5 font-mono text-xs">{{ $mapping->slug }}</td>
                            <td class="px-4 py-3.5 font-mono text-xs text-okoa-muted">{{ $mapping->package_code }}</td>
                            <td class="px-4 py-3.5">{{ $mapping->type }}</td>
                            <td class="px-4 py-3.5 text-xs">{{ $mapping->is_available ? 'yes' : 'no' }}</td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.bundle-mappings.edit', $mapping) }}" class="inline-flex min-h-9 items-center rounded-md px-2.5 py-1 text-xs font-semibold text-ok hover:bg-ok-light transition">Edit</a>
                                    <form method="POST" action="{{ route('admin.bundle-mappings.destroy', $mapping) }}" class="inline" onsubmit="return confirm('Delete this bundle mapping?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex min-h-9 items-center rounded-md px-2.5 py-1 text-xs font-semibold text-red-600 hover:bg-red-50 transition">Delete</button>
                                    </form>
                                </div>
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