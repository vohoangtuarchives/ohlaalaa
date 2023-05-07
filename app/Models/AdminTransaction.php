<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminTransaction extends Model
{
    public const ENABLE_CUSTOMER_TRANSFER_POINT = 'ENABLE_CUSTOMER_TRANSFER_POINT';
    public const DISABLE_CUSTOMER_TRANSFER_POINT = 'DISABLE_CUSTOMER_TRANSFER_POINT';

    public const ENABLE_CUSTOMER_SPECIAL_KOL = 'ENABLE_CUSTOMER_SPECIAL_KOL';
    public const DISABLE_CUSTOMER_SPECIAL_KOL = 'DISABLE_CUSTOMER_SPECIAL_KOL';


    protected $fillable = [
        'name', 'content', 'admin_id', 'admin_name'
    ];


    public function admin(){
        return $this->belongsTo(Admin::class);
    }


}
