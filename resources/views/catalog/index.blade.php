@extends('layouts.app')

@section('title', 'Okoa — Safaricom Bundles')

@section('content')
<div
    x-data="catalogApp(@js($products))"
    x-cloak
    class="max-w-4xl mx-auto px-4 sm:px-6 pb-16"
>
    <div class="pt-10 pb-8 text-center">
        <h1 class="text-3xl sm:text-4xl font-bold tracking-tight">
            Your bundles,<br class="sm:hidden"> just a few taps away.
        </h1>
        <p class="mt-3 text-base text-okoa-muted max-w-lg mx-auto">
            Buy Safaricom data, SMS, and minutes instantly with M-PESA.
        </p>
    </div>

    @if (session('error'))
        <p class="mb-4 text-center text-sm text-red-600">{{ session('error') }}</p>
    @endif

    <div class="sticky top-14 z-40 bg-okoa-bg/95 backdrop-blur py-3 -mx-4 px-4 sm:-mx-6 sm:px-6">
        <div class="relative">
            <input
                type="search"
                x-model="search"
                placeholder="Search by bundle, e.g. 1 GB, 100 SMS..."
                class="w-full rounded-lg border border-okoa-border bg-white px-4 py-3 pl-10 text-sm shadow-sm focus:border-ok focus:outline-none focus:ring-1 focus:ring-ok"
            >
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-okoa-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        <div class="mt-3 flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
            <template x-for="category in categories" :key="category.id">
                <button
                    type="button"
                    x-text="category.label"
                    x-on:click="activeCategory = category.id"
                    :class="activeCategory === category.id ? 'bg-ok text-white border-ok' : 'bg-white text-okoa-charcoal border-okoa-border hover:border-slate-300'"
                    class="whitespace-nowrap rounded-full border px-4 py-2 text-sm font-medium transition"
                ></button>
            </template>
        </div>
    </div>

    <div class="mt-6">
        <template x-if="filteredProducts.length === 0">
            <div class="py-16 text-center">
                <p class="text-okoa-muted">No bundles match your search.</p>
                <button type="button" x-on:click="search = ''; activeCategory = 'all'" class="mt-2 text-sm font-medium text-ok hover:underline">
                    Clear filters
                </button>
            </div>
        </template>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="product in filteredProducts" :key="product.slug">
                <article class="flex h-full flex-col rounded-xl border border-okoa-border bg-white p-5 shadow-sm">
                    <div class="text-3xl mb-3" x-text="resolveIcon(product.type)"></div>
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold" x-text="product.name"></h2>
                        <p class="mt-1 text-sm text-okoa-muted" x-text="safaricomType(product.type)"></p>
                        <p class="mt-1 text-sm text-okoa-muted" x-text="validityLabel(product.validity)"></p>
                    </div>
                    <div class="mt-4 flex items-center justify-between gap-3">
                        <span class="text-lg font-bold" x-text="'KSh ' + product.amount"></span>
                        <template x-if="product.is_available">
                            <a
                                :href="product.buy_url"
                                class="inline-flex min-h-11 items-center rounded-lg bg-ok px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-ok-dark transition"
                            >
                                Buy Now
                            </a>
                        </template>
                        <template x-if="!product.is_available">
                            <button type="button" disabled class="inline-flex min-h-11 items-center rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-400 cursor-not-allowed">
                                Unavailable
                            </button>
                        </template>
                    </div>
                </article>
            </template>
        </div>
    </div>

    <div class="mt-16 rounded-2xl bg-white border border-okoa-border p-6 sm:p-8">
        <h2 class="text-xl font-bold text-center">How it works</h2>
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-ok-light text-ok">
                    <span class="text-sm font-bold">1</span>
                </div>
                <h3 class="mt-3 text-sm font-semibold">Choose a bundle</h3>
                <p class="mt-1 text-sm text-okoa-muted">Pick data, SMS, or minutes for your line.</p>
            </div>
            <div class="text-center">
                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-ok-light text-ok">
                    <span class="text-sm font-bold">2</span>
                </div>
                <h3 class="mt-3 text-sm font-semibold">Pay with M-PESA</h3>
                <p class="mt-1 text-sm text-okoa-muted">Enter your phone number and confirm the STK prompt.</p>
            </div>
            <div class="text-center">
                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-ok-light text-ok">
                    <span class="text-sm font-bold">3</span>
                </div>
                <h3 class="mt-3 text-sm font-semibold">Get your bundle</h3>
                <p class="mt-1 text-sm text-okoa-muted">We deliver the bundle after payment is confirmed.</p>
            </div>
        </div>
    </div>
</div>
@endsection
