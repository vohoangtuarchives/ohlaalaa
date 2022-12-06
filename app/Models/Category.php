<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name','slug','photo','is_featured','image','handling_fee'];
    public $timestamps = false;

    public function subs()
    {
    	return $this->hasMany('App\Models\Subcategory')->where('status','=',1);
    }

    public function products()
    {
        return $this->hasMany('App\Models\Product');
    }

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = str_replace(' ', '-', $value);
    }

    public function attributes() {
        return $this->morphMany('App\Models\Attribute', 'attributable');
    }

    public function show_photo()
    {
        if(isset($this->photo)){
            if (strncmp($this->photo, "Data/", 5) === 0){
                return asset('assets/images/'.$this->photo);
            }
            else{
                return asset('assets/images/categories/'.$this->photo);
            }
        }
        return asset('assets/images/noimage.png');
    }

    public function show_image()
    {
        if(isset($this->image)){
            if (strncmp($this->image, "Data/", 5) === 0){
                return asset('assets/images/'.$this->image);
            }
            else{
                return asset('assets/images/categories/'.$this->image);
            }
        }
        return asset('assets/images/noimage.png');
    }
}
