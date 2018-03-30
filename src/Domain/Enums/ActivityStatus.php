<?php

namespace Domain\Enums;

use MyCLabs\Enum\Enum;

class ActivityStatus extends Enum
{
    const ENABLED = 'on';
    const DISABLED = 'off';
}