<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponVendorUsedLog extends Model
{
    public function writeLog($coupon, $user_id, $remarks, $desc)
    {
        $this->user_id = $user_id;
        $this->remarks = $remarks;
        $this->coupon_id = $coupon->id;
        $this->code = $coupon->code;
        $this->type = $coupon->type;
        $this->price = $coupon->price;
        $this->times = $coupon->times;
        $this->used = $coupon->used;
        $this->start_date = $coupon->start_date;
        $this->end_date = $coupon->end_date;
        $this->descriptions = $desc;
        $this->save();
    }
}
