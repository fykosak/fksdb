<?php

namespace FKSDB\Logging;

use FKSDB\Messages\Message;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * Implementations may define their own message levels.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface ILogger {

    public const ERROR = 'danger';
    public const WARNING = 'warning';
    public const SUCCESS = 'success';
    public const INFO = 'info';
    public const PRIMARY = 'primary';
    public const DEBUG = 40;

    /**
     * @param Message $message
     * @return void
     */
    public function log(Message $message);
}
