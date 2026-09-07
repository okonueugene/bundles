<?php

namespace App\Services\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AfricasTalkingAdapter implements ProviderAdapter
{
    public function getName(): string
    {
        return 'AFRICAS_TALKING';
    }

    public function topUp(string $phoneNumber, float $amount, ?string $packageCode = null): TopUpResult
    {
        $username = config('services.africastalking.username', 'sandbox');
        $apiKey = config('services.africastalking.api_key');
        $baseUrl = $username === 'sandbox'
            ? 'https://api.sandbox.africastalking.com/version1/airtime/send'
            : 'https://api.africastalking.com/version1/airtime/send';

        try {
            $response = Http::withHeaders([
                'apiKey' => $apiKey,
                'Accept' => 'application/json',
            ])->timeout(3)
              ->asForm()
              ->post($baseUrl, array_filter([
                  'username' => $username,
                  'recipients' => json_encode([
                      [
                          'phoneNumber' => $phoneNumber,
                          'currencyCode' => 'KES',
                          'amount' => (string) $amount,
                      ],
                  ]),
                  'packageCode' => $packageCode,
              ], static fn ($value) => $value !== null));

            if ($response->failed()) {
                return TopUpResult::fastFail("HTTP {$response->status()}: " . $response->body());
            }

            $data = $response->json();
            $resultEntry = $data['responses'][0] ?? null;

            if ($resultEntry && $resultEntry['status'] === 'Sent') {
                return TopUpResult::success($this->getName());
            }

            return TopUpResult::fastFail($resultEntry['errorMessage'] ?? 'Airtime delivery failed');
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return TopUpResult::timeout("Africa's Talking connection timeout");
        } catch (\Throwable $e) {
            Log::error("AT Adapter Exception: " . $e->getMessage());
            return TopUpResult::fastFail($e->getMessage());
        }
    }

    public function checkStatus(string $phoneNumber, float $amount, ?string $packageCode = null): StatusCheckResult
    {
        return StatusCheckResult::unknown();
    }

    public function sendSms(string $phoneNumber, string $message): bool
    {
        $username = config('services.africastalking.username', 'sandbox');
        $apiKey = config('services.africastalking.api_key');
        $baseUrl = $username === 'sandbox'
            ? 'https://api.sandbox.africastalking.com/version1/messaging'
            : 'https://api.africastalking.com/version1/messaging';
        $payload = [
            'username' => $username,
            'to' => $phoneNumber,
            'message' => $message,
        ];
        $senderId = config('client_sms.sender_id');

        if ($senderId) {
            $payload['from'] = $senderId;
        }

        $response = Http::withHeaders([
            'apiKey' => $apiKey,
            'Accept' => 'application/json',
        ])->timeout(5)
          ->asForm()
          ->post($baseUrl, $payload);

        return $response->successful();
    }
}
