<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Ward;

class PlaceController extends Controller
{
    public function getdistrictsbyprovinceid($province_id){
        $data = District::where('province_id','=',$province_id)->get();
        return response()->json($data);
    }

    public function getwardsbydistrictid($district_id){
        $data = Ward::where('district_id','=',$district_id)->get();
        return response()->json($data);
    }
}
