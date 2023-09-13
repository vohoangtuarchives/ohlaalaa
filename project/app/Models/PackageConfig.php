<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageConfig extends Model
{
    protected $fillable = ['name','image_url','price','remarks','content','last_update_by','sort_index','bonus_sp','bonus_rp','allow_buy','tnc','rebate_bonus', 'approval_list'];
}
