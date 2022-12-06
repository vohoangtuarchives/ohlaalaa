<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPointLog extends Model
{
    protected $fillable = ['user_id', 'order_ref_id', 'reward_point', 'reward_point_balance', 'shopping_point', 'shopping_point_balance'
        , 'exchange_rate'
        , 'cpnote_id'
        , 'shop_id'
        , 'amount'
        , 'note'
        , 'descriptions'
        , 'created_at'
        , 'updated_at'
        , 'amount_bonus'
        , 'consumer_id'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User')->withDefault(function ($data) {
			foreach($data->getFillable() as $dt){
				$data[$dt] = __('Deleted');
			}
		});
    }

    public function rebate($order, $user, $vorder, $product_amount_for_rebate, $gs){
        $this->user_id = $order->user_id;
        $this->log_type = 'Rebate Bonus';
        $this->order_ref_id = $order->id;
        $this->reward_point_balance = isset($user->reward_point) ? $user->reward_point : 0;
        $this->shopping_point_balance = isset($user->shopping_point) ? $user->shopping_point : 0;
        $this->exchange_rate = $vorder->rebate_bonus;
        $this->note = 'Paid from vendor_order ['.$vorder->id.']';
        // $this->descriptions = 'Bạn được hưởng điểm thưởng khuyến mãi cho sản phẩm ['.$vorder->product_name.'] của đơn hàng số ['.$vorder->order_number.']';
        $this->descriptions = 'Bạn được thưởng điểm thống kê RP cho sản phẩm ['.$vorder->product_name.'] của đơn hàng số ['.$vorder->order_number.']';
        $this->reward_point = 0;
        $this->shopping_point = 0;
        $this->amount = $product_amount_for_rebate;
        $this->rebate_bonus =  $vorder->rebate_bonus;
        $this->rebate_in = $vorder->rebate_in;
        $this->rebate_payment_in = $gs->rebate_payment_in;
        $this->sp_vnd_exchange_rate = $gs->sp_vnd_exchange_rate;

        switch($vorder->rebate_in){
            case 0:
                $this->reward_point = $vorder->rebate_amount;
                $user->reward_point = $user->reward_point + $vorder->rebate_amount;
                break;
            case 1:
                $this->shopping_point = $vorder->rebate_amount;
                // $point_log->shopping_point = $vorder->rebate_amount / $gs->sp_vnd_exchange_rate;
                //$consumer->shopping_point = $consumer->shopping_point + $vorder->rebate_amount /$gs->sp_vnd_exchange_rate;
                $user->shopping_point = $user->shopping_point + $vorder->rebate_amount;
                break;
        }
    }

    public function receive_payment_shopping_point($user, $order_id, $vorder, $product_amount, $gs){
        $this->user_id = $user->id;
        $this->log_type = 'Order Completed';
        $this->order_ref_id = $order_id;
        $this->reward_point_balance = isset($user->reward_point) ? $user->reward_point : 0;
        $this->shopping_point_balance = isset($user->shopping_point) ? $user->shopping_point : 0;
        $this->exchange_rate = 0;
        $this->note = 'Paid from vendor_order ['.$vorder->id.']';
        $this->descriptions = 'Bạn đã được thanh toán shopping point cho sản phẩm ['.$vorder->product_name.'] của đơn hàng số ['.$vorder->order_number.']';
        $this->reward_point = 0;
        $this->shopping_point = $vorder->shopping_point_used;
        $this->amount = $product_amount;
        $this->sp_vnd_exchange_rate = $gs->sp_vnd_exchange_rate;
    }
}
