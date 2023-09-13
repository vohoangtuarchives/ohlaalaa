<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class Rating extends Model
{
    protected $fillable = ['user_id','product_id','review','rating','review_date'];
    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo('App\Models\Product')->withDefault(function ($data) {
			foreach($data->getFillable() as $dt){
				$data[$dt] = __('Deleted');
			}
		});
    }
    public function user()
    {
        return $this->belongsTo('App\Models\User')->withDefault(function ($data) {
			foreach($data->getFillable() as $dt){
				$data[$dt] = __('Deleted');
			}
		});
    }
    public static function ratings($productid){
        return Cache::remember("cache_ratings_product_".$productid, Carbon::now()->addDay(), function() use($productid){
            $stars = Rating::where('product_id',$productid)->avg('rating');
            $ratings = number_format((float)$stars, 1, '.', '')*20;
            return $ratings;
        });
    }
    public static function rating($productid){
        return Cache::remember("cache_rating_product_".$productid, Carbon::now()->addDay(), function() use($productid){
            $stars = Rating::where('product_id',$productid)->avg('rating');
            return number_format((float)$stars, 1, '.', '');
        });
    }

}
