<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = ['title', 'details', 'faq_category_id'];
    public $timestamps = false;

    public function category()
    {
        return $this->belongsTo('App\Models\FaqCategory','faq_category_id');
    }
}
