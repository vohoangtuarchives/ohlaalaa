<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class UserRank extends Enum
{
    const Auto =   0;
    const Regular =   1;
    const Premium =   2;
    const Gold = 3;
    const Platinum = 4;

    public static function is_higher_rank($r1, $r2)
    {
        return $r1 > $r2;
    }
}
