<?php

namespace App\Jobs;

use App\Models\PaymentBatch;
use App\Models\PaymentLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessPaymentFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public function __construct(public int $batchId) {}

    public function handle(): void
    {
        $batch = PaymentBatch::findOrFail($this->batchId);
        $batch->update(['status' => 'processing']);

        $stream = Storage::disk('s3')->readStream($batch->s3_path);
        if (!$stream) {
            throw new \RuntimeException('Unable to open uploaded file');
        }

        $fh = fopen('php://temp', 'w+');
        stream_copy_to_stream($stream, $fh);
        rewind($fh);

        // Read header row (your CSV format)
        $header = fgetcsv($fh);
        if (!$header) {
            throw new \RuntimeException('Empty CSV');
        }

        $map = $this->normalizeHeaderMap($header);

        // Your required columns
        $required = [
            'customer_id',
            'customer_name',
            'customer_email',
            'amount',
            'currency',
            'reference_no',
            'date_time',
        ];

        foreach ($required as $col) {
            if (!isset($map[$col])) {
                throw new \RuntimeException("Missing required column: {$col}");
            }
        }

        $rowNum = 1; // header already read
        $queued = 0;

        while (true) {
            $row = fgetcsv($fh);
            if ($row === false) break; 

            $rowNum++;

            // 1) Skip completely empty lines (e.g., trailing blank rows)
            $hasAnyValue = false;
            foreach ($row as $v) {
                if (trim((string)$v) !== '') { $hasAnyValue = true; break; }
            }
            if (!$hasAnyValue) {
                continue;
            }

            if (count($row) < count($header)) {
                continue;
            }

       
            $payload = [
                'customer_id'    => trim($row[$map['customer_id']] ?? ''),
                'customer_name'  => trim($row[$map['customer_name']] ?? ''),
                'customer_email' => trim($row[$map['customer_email']] ?? ''),
                'amount'         => (float)($row[$map['amount']] ?? 0),
                'currency'       => strtoupper(trim($row[$map['currency']] ?? '')),
                'reference_no'   => trim($row[$map['reference_no']] ?? ''),
                'date_time'      => trim($row[$map['date_time']] ?? ''),
            ];

            
            if ($payload['customer_id'] === '' ||
                $payload['reference_no'] === '' ||
                ($payload['amount'] === 0.0 && trim((string)($row[$map['amount']] ?? '')) === '')
            ) {
                continue;
            }

            ProcessPaymentRowJob::dispatch($batch->id, $rowNum, $payload)->onQueue('payments');
            $queued++;
        }

        fclose($fh);
        if (is_resource($stream)) { fclose($stream); }

        $batch->update(['status' => 'done']);
    }

    /**
     * Normalize header cells to lowercase snake-ish keys
     *   - trims
     *   - lowercases
     *   - replaces spaces with underscores
     */
    private function normalizeHeaderMap(array $header): array
    {
        $map = [];
        foreach ($header as $i => $h) {
            $k = strtolower(trim($h ?? ''));
            $k = preg_replace('/\s+/', '_', $k);
            $map[$k] = $i;
        }
        return $map;
    }

    public function failed(\Throwable $e): void
    {
        PaymentBatch::whereKey($this->batchId)->update([
            'status' => 'failed',
            'error'  => $e->getMessage(),
        ]);
        Log::error('ProcessPaymentFileJob failed', [
            'batchId' => $this->batchId,
            'err'     => $e->getMessage(),
        ]);
    }
}
