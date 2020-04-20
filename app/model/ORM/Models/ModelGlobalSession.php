<?php

namespace FKSDB\ORM\Models;
use DateTime;
use FKSDB\ORM\AbstractModelSingle;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read DateTime until
 * @property-read DateTime since
 * @property-read integer login_id
 * @property-read integer session_id
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
