<?php
// routes/api.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentUploadController;
use App\Models\Payment;
use App\Models\PaymentBatch;
use App\Models\PaymentLog;

Route::get('/sanctum/csrf-cookie', fn() => response()->noContent()); // not strictly needed in dev



Route::post('/v2/payments/upload', [PaymentUploadController::class, 'store']); 


// Public auth
Route::post('/auth/register', [AuthController::class,'register']);
Route::post('/auth/login',    [AuthController::class,'login']);

// Authenticated
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me',     [AuthController::class,'me']);
    Route::post('/auth/logout', [AuthController::class,'logout']);

    // Protected user routes
    Route::get('/my/payments', fn() =>
        \App\Models\Payment::where('customer_email', auth()->user()->email)->latest()->paginate(20)
    );


    // Admin-only example
    Route::post('/payments/upload', [\App\Http\Controllers\PaymentUploadController::class,'store']);
    Route::get('/admin/batches', fn() => \App\Models\PaymentBatch::latest()->paginate(20));
    Route::get('/admin/batches/{id}', fn($id) =>
        \App\Models\PaymentBatch::with(['payments'=>fn($q)=>$q->latest(),'logs'=>fn($q)=>$q->latest()])->findOrFail($id)
    );

});
