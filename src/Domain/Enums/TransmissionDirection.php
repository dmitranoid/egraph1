<?php

namespace Domain\Enums;

use MyCLabs\Enum\Enum;

class TransmissionDirection extends Enum
{
    const TX = 'tx';
    const RX = 'rx';
    const BOTH = 'rxtx';
}