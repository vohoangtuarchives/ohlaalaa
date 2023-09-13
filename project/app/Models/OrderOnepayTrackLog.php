<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderOnepayTrackLog extends Model
{
    protected $table = "order_onepay_track_logs";
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
        $this->content = serialize($data);
        $this->save();
    }
    public function save_ipn_url($order_id, $url)
    {
        $this->order_id = $order_id;
        $this->title = 'URL IPN';
        $this->content = $url;
        $this->save();
    }
    
}
