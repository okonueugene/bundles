<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Okoa')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-okoa-bg text-okoa-charcoal">
    <div class="min-h-screen flex flex-col">
        <header class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-okoa-border">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
                <a href="{{ route('catalog.index') }}" class="text-xl font-bold tracking-tight text-ok">
                    Okoa
                </a>
                <a href="{{ route('track.order') }}" class="text-sm font-medium text-okoa-muted hover:text-ok transition">
                    Check Order
                </a>
            </div>
        </header>

        <main class="flex-1">
            @yield('content')
        </main>

        <footer class="border-t border-okoa-border bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 py-6">
                <p class="text-center text-xs text-okoa-muted leading-relaxed">
                    OKOA • Safaricom Data, SMS & Minutes Reseller<br>
                    Secure M-PESA Payment Processing
                </p>
            </div>
        </footer>
    </div>
</body>
</html>
