<!-- resources/views/emails/invoice.blade.php -->
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <style>
    body { font-family: Arial, Helvetica, sans-serif; }
    .box { max-width: 720px; margin: 0 auto; border: 1px solid #eee; padding: 16px; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    th, td { padding: 8px; border-bottom: 1px solid #eee; text-align: left; font-size: 14px; }
    th { background: #f8f8f8; }
    .total { text-align: right; font-weight: 700; }
  </style>
</head>
<body>
  <div class="box">
    <h2>Invoice for {{ $inv->customer_name ?? $inv->customer_email }}</h2>
    <p>Date: <strong>{{ $inv->for_date->format('Y-m-d') }}</strong></p>

    <table>
      <thead>
        <tr>
          <th>Payment Date</th>
          <th>Reference</th>
          <th>Currency</th>
          <th>Amount</th>
          <th>USD</th>
        </tr>
      </thead>
      <tbody>
        @foreach($inv->items as $it)
        <tr>
          <td>{{ \Illuminate\Support\Carbon::parse($it->payment_date)->format('Y-m-d') }}</td>
          <td>{{ $it->reference }}</td>
          <td>{{ $it->currency }}</td>
          <td>{{ number_format($it->amount, 2) }}</td>
          <td>{{ number_format($it->amount_usd, 2) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>

    <p class="total">Total (USD): {{ number_format($inv->total_usd, 2) }}</p>
  </div>
</body>
</html>
