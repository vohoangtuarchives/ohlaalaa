<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    protected $fillable = ['product_id','photo'];
    public $timestamps = false;

    public function show_photo()
    {
        if(isset($this->photo)){
            if (strncmp($this->photo, "Data/", 5) === 0){
                return asset('assets/images/'.$this->photo);
            }
            else{
                return asset('assets/images/galleries/'.$this->photo);
            }
        }
        return asset('assets/images/noimage.png');
    }
}
