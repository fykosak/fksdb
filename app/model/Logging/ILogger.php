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

    const ERROR = 'danger';
    const WARNING = 'warning';
    const SUCCESS = 'success';
    const INFO = 'info';
    const PRIMARY = 'primary';
    const DEBUG = 40;

    /**
     * @param Message $message
     * @return mixed
     */
    public function log(Message $message);
}
