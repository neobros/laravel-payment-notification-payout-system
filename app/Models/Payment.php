<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_batch_id',
        'customer_id',
        'payment_date',
        'paid_at',
        'reference',
        'customer_name',
        'customer_email',
        'currency',
        'amount',
        'amount_usd',
        'processed',
        'processed_at',
        'meta',
    ];

    protected $casts = [
        'processed'   => 'bool',
        'processed_at'=> 'datetime',
        'paid_at'     => 'datetime',
        'payment_date'=> 'date',
        'meta'        => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PaymentBatch::class, 'payment_batch_id');
    }
}
