<?php

namespace FKSDB\Logging;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * Implementations may define their own message levels.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface ILogger {

    const ERROR = 0;
    const WARNING = 10;
    const SUCCESS = 20;
    const INFO = 30;
    const DEBUG = 40;

    public function log($message, $level = self::INFO);
}
