<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTransaction extends Model
{


    public const USER_TRANSFER_POINT = 'USER_TRANSFER_POINT';

    protected $fillable = [
        'name', 'content', 'user_id', 'user_name'
    ];


    public function user(){
        return $this->belongsTo(User::class);
    }


}
