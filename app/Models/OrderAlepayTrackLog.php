<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAlepayTrackLog extends Model
{
    protected $table = "order_alepay_track_logs";
    public function save_url($order_id, $url)
    {
        $this->order_id = $order_id;
        $this->title = 'URL';
        $this->content = $url;
        $this->save();
    }

    public function save_ipn($order_id, $data)
    {
        $this->order_id = $order_id;
        $this->title = 'IPN';
        $this->content = json_encode($data[0]);
        $this->save();
    }
}
