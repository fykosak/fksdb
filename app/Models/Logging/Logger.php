<?php

namespace FKSDB\Models\Logging;

use FKSDB\Models\Messages\Message;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * Implementations may define their own message levels.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface Logger {

    public const ERROR = 'danger';
    public const WARNING = 'warning';
    public const SUCCESS = 'success';
    public const INFO = 'info';
    public const PRIMARY = 'primary';
    public const DEBUG = 40;

    public function log(Message $message): void;
}
