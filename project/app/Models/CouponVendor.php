<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CouponVendor extends Model
{
    protected $fillable = ['code', 'type', 'status', 'price', 'times', 'start_date','end_date','vendor_id','remarks','create_by','admin_issuer_id','admin_updated_date'];

    public function vendor()
    {
        return $this->belongsTo('App\Models\User','vendor_id');
    }

    public function IsValid($vendor_id)
    {
        $now = Carbon::now()->format('Y-m-d');
        return ($this->times > 0 || $this->times == null)
            && $this->status == 1
            && $this->start_date <= $now
            && $this->end_date >= $now
            && $this->vendor_id == $vendor_id;
    }
}
