<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentBatch extends Model
{
    use HasFactory;

     protected $fillable = ['original_filename','s3_path','status','error'];


     public function payments(): HasMany { return $this->hasMany(Payment::class); }

     public function logs(): HasMany { return $this->hasMany(PaymentLog::class); }
    
}
