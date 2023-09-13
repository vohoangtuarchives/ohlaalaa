<?php

namespace App\Models;
use Auth;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
	protected $fillable = ['user_id', 'cart', 'method',
        'shipping', 'pickup_location', 'totalQty',
        'pay_amount', 'txnid', 'charge_id', 'order_number',
        'payment_status', 'customer_email', 'customer_name',
        'customer_phone', 'customer_address', 'customer_city',
        'customer_zip','shipping_name', 'shipping_email',
        'shipping_phone', 'shipping_address', 'shipping_city',
        'shipping_zip', 'order_note', 'status', 'customer_province_id',
        'customer_district_id', 'customer_ward_id', 'is_send_fee',
        'shipping_province_id', 'shipping_district_id',
        'shipping_ward_id', 'is_shipdiff', 'is_online_payment',
        'payment_bank', 'products_amount', 'coupon_discount',
        'shipping_cost','pay_amount1', 'pay_amount2',
        'is_shopping_point_used','shopping_point_payment_remain', 'shopping_point_used',
        'shopping_point_amount','shopping_point_exchange_rate', 'msb_calculated',
        'completed_at',
        'ondelivery_at',
        'shipping_type',
        'consumer_money_collection_bank',
        'payment_to_company_partner',
        'payment_to_company_amount',
        'payment_to_merchant_amount',
        'vendor_discount_amount',
        'refund_date',
        'refund_amount',
        'refund_bank',
        'refund_note',
        'refund_by_name',
        'customer_received',
        'kolc_calculated',
    ];

    public function vendororders()
    {
        return $this->hasMany('App\Models\VendorOrder');
    }

    public function tracks()
    {
        return $this->hasMany('App\Models\OrderTrack','order_id');
    }

    public function admin_tracks()
    {
        return $this->hasMany('App\Models\OrderAdminTrackLog','order_id');
    }

    public function orderconsumershippingcosts()
    {
        return $this->hasMany('App\Models\OrderConsumerShippingCost','order_id');
    }

    public function handlingfeelogs(){
        return $this->hasMany('App\Models\OrderHandlingFeeLog','order_id');
    }

    public function ordervendorinfos(){
        return $this->hasMany('App\Models\OrderVendorInfo','order_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id');
    }

    public function customerward()
    {
        return $this->belongsTo('App\Models\Ward','customer_ward_id');
    }

    public function customerdistrict()
    {
        return $this->belongsTo('App\Models\District','customer_district_id');
    }

    public function customerprovince()
    {
        return $this->belongsTo('App\Models\Province','customer_province_id');
    }

    public function shippingward()
    {
        return $this->belongsTo('App\Models\Ward','shipping_ward_id');
    }

    public function shippingdistrict()
    {
        return $this->belongsTo('App\Models\District','shipping_district_id');
    }

    public function shippingprovince()
    {
        return $this->belongsTo('App\Models\Province','shipping_province_id');
    }

    public function ordervendorinfosv2() {
        return $this->hasOne('App\Models\OrderVendorInfo','order_id')
        ->where('shop_id', '=', Auth::user()->id)->first();
    }
}
