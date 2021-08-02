<?php

namespace FKSDB\Models\Logging;

use FKSDB\Models\Messages\Message;

/**
 * Implementations may define their own message levels.
 */
interface Logger
{

    public const ERROR = 'danger';
    public const WARNING = 'warning';
    public const SUCCESS = 'success';
    public const INFO = 'info';
    public const PRIMARY = 'primary';
    public const DEBUG = 40;

    public function log(Message $message): void;
}
