<?php

namespace App\Services;

use Stripe\Balance;
use Stripe\Charge;
use Stripe\Stripe;
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
        'amount' => $paymentData['amount'] * 100,
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

  public function getBalance()
  {
    try {
      $balance = Balance::retrieve();

      $available = array_map(function ($b) {
        return [
          'amount' => $b->amount,
          'currency' => $b->currency,
        ];
      }, $balance->available ?? []);

      $pending = array_map(function ($b) {
        return [
          'amount' => $b->amount,
          'currency' => $b->currency,
        ];
      }, $balance->pending ?? []);

      // Optional conversion to USD using .env rates like FX_SEK_USD=0.095 (1 SEK = 0.095 USD)
      $availableUsd = null;
      $pendingUsd = null;

      $convAvailable = 0.0;
      $convPending = 0.0;
      $canConvertAll = true;
      $usedAnyDefault = false;

      foreach ($available as $item) {
        $curr = strtoupper($item['currency']);
        $amountBase = $item['amount'] / 100.0;
        if ($curr === 'USD') {
          $convAvailable += $amountBase;
        } else {
          $usedDefault = false;
          $rate = $this->resolveUsdRate($curr, $usedDefault);
          if ($rate !== null) {
            $convAvailable += ($amountBase * $rate);
            $usedAnyDefault = $usedAnyDefault || $usedDefault;
          } else {
            $canConvertAll = false;
          }
        }
      }

      foreach ($pending as $item) {
        $curr = strtoupper($item['currency']);
        $amountBase = $item['amount'] / 100.0;
        if ($curr === 'USD') {
          $convPending += $amountBase;
        } else {
          $usedDefault = false;
          $rate = $this->resolveUsdRate($curr, $usedDefault);
          if ($rate !== null) {
            $convPending += ($amountBase * $rate);
            $usedAnyDefault = $usedAnyDefault || $usedDefault;
          } else {
            $canConvertAll = false;
          }
        }
      }

      if ($canConvertAll) {
        $availableUsd = round($convAvailable, 2);
        $pendingUsd = round($convPending, 2);
      }

      return [
        'balance' => $pendingUsd,
      ];
    } catch (Exception $e) {
      return [
        'success' => false,
        'message' => 'Failed to retrieve balance',
        'error' => $e->getMessage(),
      ];
    }
  }

  // Resolve USD conversion rate for a currency.
  // Priority: FX_<CUR>_USD -> FX_DEFAULT_USD -> built-in defaults (SEK)
  private function resolveUsdRate(string $currency, ?bool &$usedDefault = false): ?float
  {
    if (strtoupper($currency) === 'SEK') {
      $usedDefault = true;
      return 0.1;
    }

    return null;
  }
}
