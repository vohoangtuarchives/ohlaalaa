<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderConsumerShippingCost extends Model
{
    protected $fillable = ['order_id', 'shop_id', 'from_city_id','from_district_id', 'consumer_id', 'to_city_id', 'to_district_id', 'shipping_partner', 'shipping_partner_code',
        'shipping_cost',
        'to_ward_id',
        'MONEY_COLLECTION',
        'EXCHANGE_WEIGHT',
        'created_at',
        'updated_at',
        'weight',
        'status',
        'products_amount',
        'shopping_point_amount',
        'tax_amount',
        'discount_amount',
        'total_qty',
        'total_item',
        'remarks'];

    public function order()
    {
        return $this->belongsTo('App\Models\Order','order_id')->withDefault(function ($data) {
			foreach($data->getFillable() as $dt){
				$data[$dt] = __('Deleted');
			}
		});
    }

    public function shop()
    {
        return $this->belongsTo('App\Models\User','shop_id');
    }

    public function consumer()
    {
        return $this->belongsTo('App\Models\User','consumer_id');
    }

    public function fromcity()
    {
        return $this->belongsTo('App\Models\Province','from_city_id');
    }

    public function fromdistrict()
    {
        return $this->belongsTo('App\Models\District','from_district_id');
    }

    public function tocity()
    {
        return $this->belongsTo('App\Models\Province','from_city_id');
    }

    public function todistrict()
    {
        return $this->belongsTo('App\Models\District','from_district_id');
    }
}
