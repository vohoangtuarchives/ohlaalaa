<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberPackageRegister extends Model
{
    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id');
    }

    public function package_config()
    {
        return $this->belongsTo('App\Models\PackageConfig','package_config_id');
    }
}
