<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class RankingReason extends Enum
{
    const Expired =   1;
    const BuyingPackage =   2;
    const NotEnoughPremium = 3;
    const NotEnoughGold = 4;
    const EnoughPremium = 5;
    const EnoughGold = 6;
    const AdminUpdate = 7;
    const RefreshRank = 8;
}
