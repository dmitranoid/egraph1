<?php

namespace Domain\Enums;

use MyCLabs\Enum\Enum;

class TransmitionDirection extends Enum
{
    const TX = 'tx';
    const RX = 'rx';
    const BOTH = 'both';
}