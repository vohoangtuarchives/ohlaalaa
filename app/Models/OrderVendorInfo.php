<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderVendorInfo extends Model
{
    public function shop()
    {
        return $this->belongsTo('App\Models\User','shop_id');
    }
}
