<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRankingLog extends Model
{
    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id');
    }
}
