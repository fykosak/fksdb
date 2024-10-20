<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition;

use Fykosak\Utils\Logging\Message;

enum BehaviorType: string
{
    case Success = Message::LVL_SUCCESS;
    case Warning = Message::LVL_WARNING;
    case Dangerous = Message::LVL_ERROR;
    case Primary = Message::LVL_PRIMARY;
    case Default = 'secondary';
}
