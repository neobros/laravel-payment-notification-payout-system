<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Illuminate\Support\Collection;

class InvoiceBuilder
{
  /** @param Collection<int,Payment> $payments */
  public function build(string $customerEmail, ?string $customerName, \DateTimeInterface $forDate, Collection $payments): Invoice
  {
    $totalUsd = $payments->sum('amount_usd');

    $invoice = Invoice::create([
      'customer_email' => $customerEmail,
      'customer_name'  => $customerName,
      'for_date'       => $forDate,
      'total_usd'      => $totalUsd,
      'status'         => 'queued',
    ]);

    $items = $payments->map(fn($p) => [
      'invoice_id'  => $invoice->id,
      'payment_id'  => $p->id,
      'payment_date'=> $p->payment_date,
      'reference'   => $p->reference,
      'currency'    => $p->currency,
      'amount'      => $p->amount,
      'amount_usd'  => $p->amount_usd,
      'created_at'  => now(),
      'updated_at'  => now(),
    ])->all();

    InvoiceItem::insert($items);

    return $invoice->refresh();
  }
}

