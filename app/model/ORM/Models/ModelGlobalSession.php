<?php

namespace FKSDB\ORM\Models;
use DateTime;
use FKSDB\ORM\AbstractModelSingle;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-readDateTime until
 * @property-readDateTime since
 * @property-readinteger login_id
 * @property-readinteger session_id
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
