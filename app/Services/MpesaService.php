<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MpesaService
{
    public function __construct(private string $consumerKey, private string $consumerSecret, private string $shortcode, private string $passkey, private string $callbackUrl) {}

    public function initiateStkPush(string $phoneNumber, float $amount, ?string $accountReference = null): array
    {
        $token = $this->getAccessToken();

        $timestamp = now()->format('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int) $amount,
            'PartyA' => $this->normalizePhone($phoneNumber),
            'PartyB' => $this->shortcode,
            'PhoneNumber' => $this->normalizePhone($phoneNumber),
            'CallBackURL' => $this->callbackUrl,
            'AccountReference' => $accountReference ?? 'OKOA',
            'TransactionDesc' => 'Bundle Purchase',
        ];

        try {
            $response = Http::withToken($token)
                ->accept('application/json')
                ->post('https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest', $payload);

            $data = $response->json();

            if ($response->success() && isset($data['ResponseCode']) && $data['ResponseCode'] === '0') {
                return [
                    'success' => true,
                    'checkout_request_id' => $data['CheckoutRequestID'] ?? null,
                    'merchant_request_id' => $data['MerchantRequestID'] ?? null,
                    'response_code' => $data['ResponseCode'] ?? null,
                    'response_description' => $data['ResponseDescription'] ?? null,
                ];
            }

            Log::warning('M-PESA STK push failed', ['payload' => $payload, 'response' => $data]);

            return [
                'success' => false,
                'response_code' => $data['ResponseCode'] ?? null,
                'response_description' => $data['ResponseDescription'] ?? $data['errorMessage'] ?? 'Unknown error',
            ];
        } catch (\Throwable $e) {
            Log::error('M-PESA STK push exception: ' . $e->getMessage(), ['payload' => $payload]);

            return [
                'success' => false,
                'response_description' => 'Network error',
            ];
        }
    }

    private function getAccessToken(): string
    {
        return Cache::remember("mpesa:access_token", 3300, function () {
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->accept('application/json')
                ->get('https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');

            $data = $response->json();

            if (! $response->success() || empty($data['access_token'])) {
                throw new \RuntimeException('Failed to get M-PESA access token');
            }

            return $data['access_token'];
        });
    }

    private function normalizePhone(string $phoneNumber): string
    {
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        if (str_starts_with($phoneNumber, '0')) {
            return '254' . substr($phoneNumber, 1);
        }

        if (str_starts_with($phoneNumber, '254')) {
            return $phoneNumber;
        }

        if (str_starts_with($phoneNumber, '+254')) {
            return substr($phoneNumber, 1);
        }

        return $phoneNumber;
    }
}
