<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberPackageAlepayTrackLog extends Model
{
    protected $table = "member_package_alepay_track_logs";
    public function save_url($mpr_id, $url)
    {
        $this->mpr_id = $mpr_id;
        $this->title = 'URL';
        $this->content = $url;
        $this->save();
    }

    public function save_ipn($mpr_id, $data)
    {
        $this->mpr_id = $mpr_id;
        $this->title = 'IPN';
        $this->content = serialize($data);
        $this->save();
    }
}
