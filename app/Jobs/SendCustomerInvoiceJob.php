<?php

namespace App\Jobs;

use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoiceBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendCustomerInvoiceJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public function __construct(
    public string $customerEmail,
    public ?string $customerName,
    public string $forDate 
  ) {}

  public function handle(InvoiceBuilder $builder): void
  {
    $payments = Payment::query()
      ->whereDate('payment_date', $this->forDate)
      ->where('customer_email', $this->customerEmail)
      ->where('processed', false)
      ->orderBy('payment_date')
      ->get();

    if ($payments->isEmpty()) return;

    $invoice = $builder->build($this->customerEmail, $this->customerName, new \DateTimeImmutable($this->forDate), $payments);

    // send
    Mail::to($this->customerEmail)->send(new InvoiceMail($invoice->fresh('items')));

    // mark processed
    Payment::whereKey($payments->pluck('id'))->update([
      'processed' => true,
      'processed_at' => now(),
    ]);

    $invoice->update(['status'=>'sent','sent_at'=>now()]);
  }
}


