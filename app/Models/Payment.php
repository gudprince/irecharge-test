<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'reference',
        'amount',
        'product_id',
        'payment_method',
        'currency',
        'customer_email',
        'customer_id',
        'status',
        'paid_at',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
