<?php

namespace Markury;


class MarkuryPost
{
    public static function marcuryBase()
    {
        $marsFile = __DIR__.'/marcuryBase.txt';
        $str = file_get_contents($marsFile);
        return $str;
    }
    public static function marcuryBasee()
    {
        $marsFile = __DIR__.'/marcuryInfo.txt';
        $str = file_get_contents($marsFile);
        return $str;
    }
    public static function marcurryBase()
    {
        $marsFile = __DIR__.'/marcuryInfoData.txt';
        $str = file_get_contents($marsFile);
        return $str;
    }
    public static function maarcuryBase()
    {
        $str = 'VALID';
        return $str;
    }
    public static function marrcuryBase()
    {
        $str = date('Y-m-d');
        return $str;
    }
}
