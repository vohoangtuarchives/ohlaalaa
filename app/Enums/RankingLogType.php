<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class RankingLogType extends Enum
{
    const Upgrade =   1;
    const Downgrade =   2;
    const Extend = 3;
    const Other = 4;
}
