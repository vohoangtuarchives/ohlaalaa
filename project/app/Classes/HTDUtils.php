<?php

namespace App\Classes;

class HTDUtils {
    public static function addDays($d, $num)
    {
        return date("Y-m-d",strtotime($d . '+ '.$num.'days'));
    }

    public static function daysDiff($sf, $st)
    {
        $to = \Carbon\Carbon::createFromFormat('Y-m-d', $st);
        $from = \Carbon\Carbon::createFromFormat('Y-m-d', $sf);
        $diff_in_days = $to->diffInDays($from);
        return $diff_in_days;
    }

    public static function carbonGetDate($d)
    {
        return \Carbon\Carbon::parse($d);
    }

    public static function carbonDaysDiff($df, $dt)
    {
        $diff_in_days = $dt->diffInDays($df);
        return $diff_in_days;
    }

    public static function carbonAddDays($d, $num)
    {
        return $d->addDays($num);
    }

}
