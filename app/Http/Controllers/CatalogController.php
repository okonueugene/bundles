<?php

namespace App\Http\Controllers;

use App\Models\BundleMapping;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(): View
    {
        $products = BundleMapping::query()
            ->where('network', 'safaricom')
            ->orderBy('amount')
            ->get()
            ->map(function (BundleMapping $product) {
                return [
                    'slug' => $product->slug,
                    'name' => $product->description,
                    'type' => $product->type,
                    'validity' => $product->validity,
                    'amount' => (int) $product->amount,
                    'is_available' => $product->is_available,
                    'buy_url' => $product->is_available ? route('buy', $product) : null,
                ];
            });

        return view('catalog.index', [
            'products' => $products,
        ]);
    }

    public function buy(BundleMapping $product)
    {
        if ($product->network !== 'safaricom' || ! $product->is_available) {
            return redirect()
                ->route('catalog.index')
                ->with('error', 'This bundle is currently unavailable.');
        }

        return view('checkout.show', [
            'product' => $product,
        ]);
    }
}
