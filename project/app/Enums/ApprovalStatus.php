<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class ApprovalStatus extends Enum
{
    const Pending = 1;
    const Approved = 2;
    const Rejected = 3;
}
