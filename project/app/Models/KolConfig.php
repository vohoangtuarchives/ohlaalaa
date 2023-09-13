<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KolConfig extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'kol_date'
        ,'con_bonus_l1'
        ,'number_users_l1'
        ,'number_orders_l1'
        ,'number_shops_l1'
        ,'avg_amount_order_l1'
        ,'number_affiliate_member_l1'
        ,'con_bonus_l2'
        ,'number_users_l2'
        ,'number_orders_l2'
        ,'number_shops_l2'
        ,'avg_amount_order_l2'
        ,'number_affiliate_member_l2'
        ,'created_at'
        ,'revenue_l1'
        ,'revenue_l2'
    ];


}
