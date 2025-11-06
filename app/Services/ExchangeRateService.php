<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class ExchangeRateService
{
  // public function getRatesForDay(string $base = 'USD'): array
  // {
 
  //     $resp = Http::get('https://api.exchangerate.host/live?access_key=8f05556bddb8d022a1d2f72c791e9d95');

  //     if (!$resp->ok()) throw new \RuntimeException('Failed to fetch exchange rates');
  //     return $resp->json('quotes') ?? [];
  
  // }

  
  public function getRatesForDay(string $base = 'USD'): array
  {
    $cacheKey = "rates:{$base}:".now()->toDateString();

    return Cache::remember($cacheKey, now()->endOfDay(), function () use ($base) {
      $resp = Http::timeout(10)->get('https://api.exchangerate.host/live?access_key=8f05556bddb8d022a1d2f72c791e9d95');
      if (!$resp->ok()) throw new \RuntimeException('Failed to fetch exchange rates');
      return $resp->json('quotes') ?? [];
    });
  }

  public function toUSD(float $amount, string $currency): float
  {
      $currency = strtoupper($currency);

      if ($currency === 'USD') {
          return $amount;
      }

      // your existing API call returns "quotes"
      $rates = $this->getRatesForDay('USD');

      // API gives: "USDAUD", "USDEUR", etc.
      $key = 'USD' . $currency;
Log::error( $rates );
      if (!isset($rates[$key])) {
          throw new \RuntimeException("Missing FX quote key: {$key}");
      }

      $rate = (float)$rates[$key]; // 1 USD = rate(CUR)

      if ($rate <= 0) {
          throw new \RuntimeException("Invalid FX rate for {$currency}");
      }

      // Convert CUR → USD
      // Example: amount AUD / USDAUD
      return round($amount / $rate, 6);
  }

}
