<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\FulfillmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaStkCallbackController extends Controller
{
    public function handle(Request $request, FulfillmentService $fulfillmentService): JsonResponse
    {
        $callback = $request->input('Body.stkCallback');

        $checkoutRequestId = is_array($callback) ? ($callback['CheckoutRequestID'] ?? null) : null;
        $resultCode = is_array($callback) ? ($callback['ResultCode'] ?? null) : null;

        if (! $checkoutRequestId || $resultCode === null) {
            Log::warning('Malformed STK callback payload', ['payload' => $request->all()]);

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        $transaction = Transaction::where('checkout_request_id', $checkoutRequestId)->first();

        if (! $transaction) {
            Log::warning("STK callback for unknown CheckoutRequestID: {$checkoutRequestId}");

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        if ($transaction->status !== 'pending') {
            Log::info("Duplicate/late STK callback for already-processed transaction {$transaction->order_reference}");

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        if ((int) $resultCode !== 0) {
            $transaction->update(['status' => 'payment_failed']);

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        $metadata = collect($callback['CallbackMetadata']['Item'] ?? [])
            ->pluck('Value', 'Name');
        $receipt = $metadata->get('MpesaReceiptNumber');

        if (! $receipt) {
            Log::error("STK success callback missing MpesaReceiptNumber for {$transaction->order_reference}");
            $transaction->update(['status' => 'needs_attention']);

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        $transaction->update(['mpesa_receipt_number' => $receipt]);
        $fulfillmentService->attemptCascade($transaction);

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
