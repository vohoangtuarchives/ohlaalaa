<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateLevelConfig extends Model
{
    protected $fillable = ['name','level','level_value'];
    public $timestamps = false;
}
