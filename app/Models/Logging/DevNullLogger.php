<?php

declare(strict_types=1);

namespace FKSDB\Models\Logging;

use FKSDB\Models\Messages\Message;

class DevNullLogger extends StackedLogger
{

    protected function doLog(Message $message): void
    {
        /* empty */
    }
}
