@php
    $current = $current ?? '';
@endphp
<div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-okoa-border pb-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-okoa-charcoal">{{ $title }}</h1>
        @if (! empty($subtitle))
            <p class="text-sm text-okoa-muted">{{ $subtitle }}</p>
        @endif
    </div>
    <nav class="flex space-x-6 mt-4 md:mt-0">
        <a href="{{ route('admin.orders.index') }}" class="text-sm font-medium pb-1 {{ $current === 'orders' ? 'text-ok border-b-2 border-ok' : 'text-okoa-muted hover:text-ok transition' }}">
            Orders
        </a>
        <a href="{{ route('admin.alerts.index') }}" class="text-sm font-medium pb-1 {{ $current === 'alerts' ? 'text-ok border-b-2 border-ok' : 'text-okoa-muted hover:text-ok transition' }}">
            Alerts
        </a>
        <a href="{{ route('admin.bundle-mappings.index') }}" class="text-sm font-medium pb-1 {{ $current === 'bundles' ? 'text-ok border-b-2 border-ok' : 'text-okoa-muted hover:text-ok transition' }}">
            Bundles
        </a>
    </nav>
</div>
