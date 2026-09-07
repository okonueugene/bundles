<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackOrderController extends Controller
{
    public function index(): View
    {
        return view('track-order.index');
    }

    public function lookup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_reference' => 'required|string|max:32',
        ]);

        $reference = strtoupper(trim($validated['order_reference']));

        $transaction = Transaction::where('order_reference', $reference)->first();

        if (! $transaction) {
            return redirect()
                ->route('track.order')
                ->withInput()
                ->with('error', 'Order not found. Please check your reference and try again.');
        }

        return redirect()->route('orders.show', $transaction->order_reference);
    }
}
