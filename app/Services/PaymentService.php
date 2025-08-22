<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Charge;
use Exception;

class PaymentService
{
  public function __construct()
  {
    Stripe::setApiKey(env('STRIPE_SECRET'));
  }

  public function processPayment($paymentData)
  {
    try {
      $charge = Charge::create([
        'amount' => $paymentData['amount'],
        'currency' => 'usd',
        'source' => $paymentData['stripeToken'],
        'description' => 'FlowTrip Payment'
      ]);

      if ($charge->status === 'succeeded') {
        return [
          'success' => true,
          'payment_id' => $charge->id,
          'amount' => $charge->amount,
          'message' => 'Payment successful'
        ];
      } else {
        return [
          'success' => false,
          'message' => 'Payment was not successful',
          'error' => 'Payment status: ' . $charge->status
        ];
      }
    } catch (Exception $e) {
      return [
        'success' => false,
        'message' => 'Payment failed',
        'error' => $e->getMessage()
      ];
    }
  }
}
