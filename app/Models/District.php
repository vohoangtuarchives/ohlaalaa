<?php

namespace App\Models;

use App\Models\Ward;
use App\Models\Province;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    public function province(){
        return $this->belongsTo(Province::class);
    }

    public function wards(){
        return $this->hasMany(Ward::class);
    }
}
