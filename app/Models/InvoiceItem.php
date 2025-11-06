<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = ['invoice_id','payment_id','payment_date','reference','currency','amount','amount_usd'];
    protected $casts = ['payment_date'=>'date'];
    
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    
}
