<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BundleMapping;
use App\Models\Transaction;
use App\Support\CustomerOrderStatus;
use Illuminate\Http\JsonResponse;

class OrderStatusController extends Controller
{
    public function show(string $reference): JsonResponse
    {
        $transaction = Transaction::where('order_reference', $reference)->firstOrFail();

        $product = BundleMapping::forOrder(
            (string) $transaction->package_code,
            $transaction->amount
        );

        $status = CustomerOrderStatus::for($transaction, $product);

        return response()->json([
            'reference' => $transaction->order_reference,
            'status' => $status['key'],
            'title' => $status['title'],
            'subtitle' => $status['subtitle'],
            'message' => $status['message'],
            'is_terminal' => $status['is_terminal'],
        ]);
    }
}
