<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\BundleMapping;
use App\Models\Transaction;
use App\Services\MpesaService;
use App\Support\CustomerOrderStatus;
use App\Support\KenyanPhone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private MpesaService $mpesaService) {}

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $product = BundleMapping::query()
            ->where('slug', $request->validated('product'))
            ->where('network', 'safaricom')
            ->firstOrFail();

        if (! $product->is_available) {
            return redirect()
                ->route('catalog.index')
                ->with('error', 'This bundle is currently unavailable.');
        }

        $phone = KenyanPhone::normalize($request->validated('phone'));

        $lock = Cache::lock('order-submit:'.$phone.':'.$product->id, 20);

        if (! $lock->get()) {
            return back()
                ->withInput()
                ->with('error', 'Please wait, your payment request is already being sent.');
        }

        try {
            $transaction = Transaction::query()
                ->where('phone_number', $phone)
                ->where('amount', $product->amount)
                ->where('status', 'pending')
                ->whereNull('mpesa_receipt_number')
                ->where('created_at', '>=', now()->subMinutes(10))
                ->latest()
                ->first();

            if (! $transaction) {
                $transaction = Transaction::create([
                    'order_reference' => $this->uniqueOrderReference(),
                    'phone_number' => $phone,
                    'amount' => $product->amount,
                    'package_code' => $product->package_code,
                    'status' => 'pending',
                ]);
            }

            if ($transaction->checkout_request_id !== null) {
                return back()
                    ->withInput()
                    ->with('error', 'A payment prompt is already active on your phone. Please complete or cancel it before trying again.');
            }

            $stkResult = $this->mpesaService->initiateStkPush(
                $transaction->phone_number,
                (float) $product->amount,
                $transaction->order_reference
            );

            if (! ($stkResult['success'] ?? false)) {
                Log::warning('M-PESA STK push was not accepted', [
                    'order_reference' => $transaction->order_reference,
                ]);

                return back()
                    ->withInput()
                    ->with('error', 'We could not send the M-PESA prompt. Please try again.');
            }

            $transaction->update([
                'checkout_request_id' => $stkResult['checkout_request_id'] ?? null,
            ]);

            return redirect()->route('payment.waiting', $transaction->order_reference);
        } catch (\Throwable $e) {
            Log::error('Order payment request failed: '.$e->getMessage(), [
                'product' => $product->slug,
            ]);

            return back()
                ->withInput()
                ->with('error', 'We could not send the M-PESA prompt. Please try again.');
        } finally {
            $lock->release();
        }
    }

    public function show(string $orderReference): View
    {
        $transaction = Transaction::where('order_reference', $orderReference)->firstOrFail();
        $product = $this->productFor($transaction);
        $status = CustomerOrderStatus::for($transaction, $product);

        return view('orders.show', compact('transaction', 'product', 'status'));
    }

    private function productFor(Transaction $transaction): ?BundleMapping
    {
        return BundleMapping::forOrder(
            (string) $transaction->package_code,
            $transaction->amount
        );
    }

    private function uniqueOrderReference(): string
    {
        do {
            $reference = 'OKOA-'.strtoupper(Str::random(8));
        } while (Transaction::where('order_reference', $reference)->exists());

        return $reference;
    }
}
