<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_number',
        'source',
        'delivery_date',
        'delivery_time',
        'products',
        'city',
        'postal_code',
        'delivery_partners',
        'status',
        'pl',
        'mc',
        'do',
    ];
}
