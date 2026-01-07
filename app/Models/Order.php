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
    protected $table = 'shopify_orders';
    
    public $timestamps = true;
    
    protected $fillable = [
        'order_id',
        'order_number',
        'lineclear_waybill_no',
        'lalamove_order_id',
        'lalamove_driver_info',
        'detrack_assigned_to',
        'email',
        'customer_name',
        'products',
        'total_price',
        'financial_status',
        'fulfillment_status',
        'shipment_status',
        'delivery_date',
        'delivery_time',
        'delivery_partner',
        'source',
        'city',
        'subzone',
        'zone',
        'postal_code',
        'pl_no',
        'mc_no',
        'do_no',
        'raw_json',
    ];
}
