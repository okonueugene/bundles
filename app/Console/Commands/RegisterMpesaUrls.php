<?php

namespace App\Console\Commands;

use App\Services\MpesaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class RegisterMpesaUrls extends Command
{
    protected $signature = 'mpesa:register-urls';

    protected $description = 'Register C2B ConfirmationURL and ValidationURL with Daraja sandbox.';

    public function handle(MpesaService $mpesa): int
    {
        $baseUrl = rtrim((string) config('services.mpesa.callback_base_url'), '/');
        $secretToken = (string) config('services.mpesa.secret_token');

        if ($baseUrl === '' || $secretToken === '') {
            $this->error('MPESA_CALLBACK_BASE_URL and MPESA_SECRET_TOKEN must be configured.');

            return self::FAILURE;
        }

        $response = Http::withToken($mpesa->getAccessToken())
            ->acceptJson()
            ->post('https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl', [
                'ShortCode' => config('services.mpesa.c2b_shortcode'),
                'ResponseType' => 'Completed',
                'ConfirmationURL' => "{$baseUrl}/api/v1/c2b/confirm?token={$secretToken}",
                'ValidationURL' => "{$baseUrl}/api/v1/c2b/validate?token={$secretToken}",
            ]);

        $this->line($response->body());

        return $response->successful() ? self::SUCCESS : self::FAILURE;
    }
}
