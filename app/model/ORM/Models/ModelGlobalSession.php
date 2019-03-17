<?php

namespace FKSDB\ORM\Models;
use DateTime;
use FKSDB\ORM\AbstractModelSingle;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property DateTime until
 * @property DateTime since
 * @property integer login_id
 * @property integer session_id
 *
 */
class ModelGlobalSession extends AbstractModelSingle {
    /**
     * @return bool
     */
    public function isValid() {
        $now = time();
        return ($this->until->getTimestamp() >= $now) && ($this->since->getTimestamp() <= $now);
    }
}
