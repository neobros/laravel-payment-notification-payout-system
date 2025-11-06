<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentLog extends Model
{
    use HasFactory;

        protected $fillable = ['payment_batch_id','payment_id','row_ref','status','message','payload'];
        protected $casts = ['payload'=>'array'];

        
        public function batch(): BelongsTo { return $this->belongsTo(PaymentBatch::class); }

}
