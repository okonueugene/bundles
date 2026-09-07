<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Transaction::query()->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_reference', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('mpesa_receipt_number', 'like', "%{$search}%");
            });
        }

        return view('admin.orders.index', [
            'transactions' => $query->paginate(25)->withQueryString(),
            'statusFilter' => $request->query('status'),
            'search' => $request->query('search'),
        ]);
    }

    public function show(Transaction $transaction): View
    {
        return view('admin.orders.show', ['transaction' => $transaction]);
    }

    public function resolve(Request $request, Transaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'resolution_status' => 'required|in:fulfilled,needs_attention',
            'resolution_note' => 'required|string|max:1000',
        ]);

        $transaction->update([
            'status' => $validated['resolution_status'],
            'manually_resolved_at' => now(),
            'resolution_note' => $validated['resolution_note'],
        ]);

        return redirect()
            ->route('admin.orders.show', $transaction)
            ->with('success', 'Order updated.');
    }

    public function alerts(): View
    {
        $alerted = Transaction::query()
            ->whereNotNull('alert_sent_at')
            ->latest('alert_sent_at')
            ->paginate(25);

        return view('admin.alerts.index', ['transactions' => $alerted]);
    }
}
