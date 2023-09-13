<?php

namespace App\Models;

use App\Models\District;
use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    public function district(){
        return $this->belongsTo(District::class);
    }
}
