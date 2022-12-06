<?php

namespace App\Classes;

class HTDPhoto {
    public function show_photo($photo, $place) {
        if(isset($photo)){
            if (strncmp($photo, "Data/", 5) === 0){
                return asset('assets/images/'.$photo);
            }
            else{
                return asset('assets/images/'.$place.'/'.$photo);
            }
        }
        return asset('assets/images/noimage.png');
    }
}
