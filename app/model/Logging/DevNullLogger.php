<?php

namespace FKSDB\Logging;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class DevNullLogger extends StackedLogger {

    /**
     * @param $message
     * @param $level
     * @return mixed|void
     */
    protected function doLog($message, $level) {
        /* empty */
    }

}
