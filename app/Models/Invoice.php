<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = ['customer_email','customer_name','for_date','total_usd','status','sent_at'];
    protected $casts = ['for_date'=>'date','sent_at'=>'datetime'];
    
    public function items(): HasMany { return $this->hasMany(InvoiceItem::class); }
}
