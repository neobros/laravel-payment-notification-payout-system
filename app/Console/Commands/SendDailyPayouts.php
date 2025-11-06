<?php
// app/Console/Commands/SendDailyPayouts.php
namespace App\Console\Commands;

use App\Jobs\SendCustomerInvoiceJob;
use App\Models\Payment;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class SendDailyPayouts extends Command
{
  protected $signature = 'payouts:daily {--date=}';
  protected $description = 'Group unprocessed payments by customer and email invoices';

  public function handle(): int
  {
    $for = $this->option('date') ?: now('Asia/Colombo')->toDateString();

    $groups = Payment::query()
      ->selectRaw('customer_email, max(customer_name) as customer_name')
      ->whereDate('payment_date', $for)
      ->where('processed', false)
      ->groupBy('customer_email')
      ->get();

    foreach ($groups as $g) {
      SendCustomerInvoiceJob::dispatch($g->customer_email, $g->customer_name, $for)->onQueue('emails');
    }

    $this->info("Queued ".count($groups)." customer invoices for {$for}");
    return self::SUCCESS;
  }
}


