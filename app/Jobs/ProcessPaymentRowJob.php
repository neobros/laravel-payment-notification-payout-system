<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Models\PaymentBatch;
use App\Models\PaymentLog;
use App\Services\ExchangeRateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPaymentRowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $batchId,
        public int $rowNum,
        public array $payload
    ) {}

    public function handle(ExchangeRateService $fx): void
    {
        $batch = PaymentBatch::findOrFail($this->batchId);
        $d = $this->payload;
        $errors = [];

        // date_time → paid_at + payment_date
        $paidAt = $this->parseDateTime($d['date_time'] ?? null);
        if (!$paidAt) $errors[] = 'Invalid date_time';

        if (!filter_var($d['customer_email'] ?? '', FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email';
        if (!is_numeric($d['amount'] ?? null)) $errors[] = 'Invalid amount';
        if (empty($d['reference_no'])) $errors[] = 'reference_no required';
        if (!preg_match('/^[A-Z]{3}$/', $d['currency'] ?? '')) $errors[] = 'Currency must be 3-letter code';

        if ($errors) {
            PaymentLog::create([
                'payment_batch_id' => $batch->id,
                'status'           => 'failed',
                'row_ref'          => "row {$this->rowNum}",
                'message'          => implode('; ', $errors),
                'payload'          => $d,
            ]);
            return;
        }

        // FX conversion (to USD)
        $amountUsd = $fx->toUSD((float) $d['amount'], $d['currency']);
        $paymentDate = $paidAt->toDateString();

        // Store idempotently (reference + email)
        $payment = Payment::firstOrCreate(
            [
                'reference'      => $d['reference_no'],
                'customer_email' => $d['customer_email'],
            ],
            [
                'payment_batch_id' => $batch->id,
                'customer_id'      => $d['customer_id'] ?? null,
                'customer_name'    => $d['customer_name'] ?? null,
                'payment_date'     => $paymentDate,
                'paid_at'          => $paidAt,
                'currency'         => $d['currency'],
                'amount'           => $d['amount'],
                'amount_usd'       => $amountUsd,
                'processed'        => false,
                'meta'             => ['source_headers' => 'v2'],
            ]
        );

        PaymentLog::create([
            'payment_batch_id' => $batch->id,
            'payment_id'       => $payment->id,
            'row_ref'          => "row {$this->rowNum}",
            'status'           => 'success',
            'message'          => 'Stored',
            'payload'          => ['amount_usd' => $amountUsd],
        ]);
    }

    /**
     * Accept common Excel/CSV date-time formats (24h & 12h)
     */
    private function parseDateTime(?string $val): ?\Illuminate\Support\Carbon
    {
        if (!$val) return null;

        $candidates = [
            'n/j/Y H:i',  'n/d/Y H:i',
            'm/d/Y H:i',  'm/j/Y H:i',
            'Y-m-d H:i',
            'n/j/Y g:i A','n/d/Y g:i A',
            'm/d/Y g:i A','Y-m-d g:i A',
        ];

        foreach ($candidates as $fmt) {
            try {
                return \Illuminate\Support\Carbon::createFromFormat($fmt, trim($val), 'UTC');
            } catch (\Throwable) {}
        }

        try { return \Illuminate\Support\Carbon::parse($val); } catch (\Throwable) {}
        return null;
    }
}
