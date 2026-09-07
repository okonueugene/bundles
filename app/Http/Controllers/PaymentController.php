<?php

namespace App\Http\Controllers;

use App\Models\BundleMapping;
use App\Models\Transaction;
use App\Support\KenyanPhone;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function waiting(string $orderReference): View
    {
        $transaction = Transaction::where('order_reference', $orderReference)->firstOrFail();

        $product = BundleMapping::forOrder(
            (string) $transaction->package_code,
            $transaction->amount
        );

        return view('payment.waiting', [
            'transaction' => $transaction,
            'product' => $product,
            'displayPhone' => KenyanPhone::formatLocal($transaction->phone_number),
        ]);
    }
}
