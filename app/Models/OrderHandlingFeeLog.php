<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderHandlingFeeLog extends Model
{
    public function order()
    {
        return $this->belongsTo('App\Models\Order','order_id');
    }

    public function shop()
    {
        return $this->belongsTo('App\Models\User','shop_id');
    }

    public function issuer()
    {
        return $this->belongsTo('App\Models\Admin','issuer_id');
    }
}
