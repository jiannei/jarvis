<?php

namespace App\Enums;

use Jiannei\Enum\Laravel\Enum;

class QueueEnum extends Enum
{
    public const LOG = 'log';

    public const NOTIFY = 'notify';
}
