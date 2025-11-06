<?php

// app/Http/Controllers/PaymentUploadController.php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests\UploadPaymentCsvRequest;
use App\Jobs\ProcessPaymentFileJob;
use App\Models\PaymentBatch;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class PaymentUploadController extends Controller
{
  public function store(UploadPaymentCsvRequest $request)
  {
    
    $file = $request->file('file');
    $path = 'payment_uploads/'.now()->format('Y/m/d').'/'.uniqid().'_'.$file->getClientOriginalName();

    // Save to S3
    try {

      $result = Storage::disk('s3')->put($path, file_get_contents($file), ['visibility'=>'private']);

      if (!$result) {
          return response()->json(['ok' => false, 'msg' => 'put returned false', 'path' => $path], 500);
      }

    } catch (\Throwable $e) {
        Log::error('S3 upload error', ['error' => $e->getMessage()]);
        return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
    }

    $batch = PaymentBatch::create([
      'original_filename' => $file->getClientOriginalName(),
      's3_path' => $path,
      'status' => 'queued',
    ]);

    ProcessPaymentFileJob::dispatch($batch->id)->onQueue('payments');

    return response()->json([
      'message' => 'File uploaded and queued for processing.',
      'batch_id' => $batch->id,
      's3_path' => $path
    ], 202);
  }

}
