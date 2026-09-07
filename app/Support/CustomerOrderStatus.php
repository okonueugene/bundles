<?php

namespace App\Support;

use App\Models\BundleMapping;
use App\Models\Transaction;

class CustomerOrderStatus
{
    /**
     * @return array{
     *     key: string,
     *     title: string,
     *     subtitle: ?string,
     *     message: string,
     *     is_terminal: bool
     * }
     */
    public static function for(Transaction $transaction, ?BundleMapping $product = null): array
    {
        $productName = $product?->description ?? 'bundle';
        $phone = KenyanPhone::formatLocal($transaction->phone_number);

        if ($transaction->status === 'fulfilled') {
            return [
                'key' => 'fulfilled',
                'title' => 'Payment successful',
                'subtitle' => 'Bundle delivered',
                'message' => "Your {$productName} bundle has been sent to {$phone}.",
                'is_terminal' => true,
            ];
        }

        if (in_array($transaction->status, ['needs_attention', 'over_fulfillment_flagged'], true)) {
            $key = $transaction->status === 'over_fulfillment_flagged'
                ? 'fulfillment_failed'
                : 'needs_attention';

            return [
                'key' => $key,
                'title' => 'We are reviewing your order.',
                'subtitle' => null,
                'message' => 'Your payment has been recorded. Our team will complete the delivery or contact you if more information is needed.',
                'is_terminal' => true,
            ];
        }

        if ($transaction->status === 'cancelled') {
            return [
                'key' => 'cancelled',
                'title' => 'This order was cancelled',
                'subtitle' => null,
                'message' => 'This order is no longer active. You can buy another bundle whenever you are ready.',
                'is_terminal' => true,
            ];
        }

        if ($transaction->status === 'payment_failed') {
            return [
                'key' => 'payment_failed',
                'title' => 'Payment was not completed',
                'subtitle' => null,
                'message' => 'The M-PESA payment was cancelled or timed out. You can try again.',
                'is_terminal' => true,
            ];
        }

        if ($transaction->status === 'queued_for_retry') {
            return [
                'key' => 'fulfillment_pending',
                'title' => 'Payment successful',
                'subtitle' => 'Delivery is taking longer than expected',
                'message' => 'Your payment is safe. We are still processing the bundle and will notify you by SMS.',
                'is_terminal' => false,
            ];
        }

        if ($transaction->status === 'processing' || ($transaction->status === 'pending' && $transaction->mpesa_receipt_number)) {
            $key = $transaction->mpesa_receipt_number && $transaction->status === 'pending'
                ? 'paid'
                : 'fulfillment_pending';

            return [
                'key' => $key,
                'title' => 'Payment successful',
                'subtitle' => 'Bundle being delivered',
                'message' => 'Your payment is confirmed. We are now delivering your bundle.',
                'is_terminal' => false,
            ];
        }

        return [
            'key' => 'pending',
            'title' => 'Payment request sent',
            'subtitle' => null,
            'message' => 'We are waiting for your M-PESA payment to be confirmed.',
            'is_terminal' => false,
        ];
    }
}
