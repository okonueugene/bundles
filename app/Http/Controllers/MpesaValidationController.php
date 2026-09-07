<?php

namespace App\Http\Controllers;

use App\Models\BundleMapping;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaValidationController extends Controller
{
    public function validate(Request $request): JsonResponse
    {
        $configuredToken = config('services.mpesa.secret_token');

        if (empty($configuredToken) || $request->query('token') !== $configuredToken) {
            return response()->json(['ResultCode' => 'C2B00016', 'ResultDesc' => 'Rejected']);
        }

        $payload = $request->all();
        $amount = $payload['TransAmount'] ?? null;

        if (! $amount) {
            Log::warning('ValidationURL called with missing TransAmount', ['payload' => $payload]);

            return response()->json(['ResultCode' => 'C2B00013', 'ResultDesc' => 'Rejected']);
        }

        $resolution = BundleMapping::resolveForAmount((float) $amount);

        if ($resolution['package_code'] === null) {
            Log::info("ValidationURL rejected unmappable amount: {$amount} ({$resolution['reason']})");

            return response()->json(['ResultCode' => 'C2B00013', 'ResultDesc' => 'Rejected']);
        }

        return response()->json(['ResultCode' => '0', 'ResultDesc' => 'Accepted']);
    }
}
