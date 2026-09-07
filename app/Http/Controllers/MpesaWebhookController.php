<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\FulfillmentService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaWebhookController extends Controller
{
    public function handleConfirm(Request $request, FulfillmentService $fulfillmentService): JsonResponse
    {
        $configuredToken = config('services.mpesa.secret_token');

        if (empty($configuredToken) || $request->query('token') !== $configuredToken) {
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Unauthorized'], 401);
        }

        $payload = $request->all();
        $receipt = $payload['TransID'] ?? null;
        $phoneNumber = $payload['MSISDN'] ?? null;
        $amount = $payload['TransAmount'] ?? null;

        if (!$receipt || !$phoneNumber || !$amount) {
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Invalid Payload'], 400);
        }

        try {
            $transaction = Transaction::create([
                'mpesa_receipt_number' => $receipt,
                'phone_number' => $phoneNumber,
                'amount' => $amount,
                'status' => 'pending',
                'raw_payload' => $payload,
            ]);
        } catch (QueryException $e) {
            $errorCode = $e->errorInfo[1] ?? null;

            if ($errorCode === 1062) {
                Log::warning("Duplicate M-PESA callback received for receipt: {$receipt}");
                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Already Processed']);
            }

            Log::error("Database insert error for receipt {$receipt}: " . $e->getMessage());
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Internal Server Error'], 500);
        }

        $fulfillmentService->attemptCascade($transaction);

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
