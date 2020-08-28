<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read \DateTimeInterface until
 * @property-read \DateTimeInterface since
 * @property-read int login_id
 * @property-read int session_id
 *
 */
class ModelGlobalSession extends AbstractModelSingle {

    public function isValid(): bool {
        $now = time();
        return ($this->until->getTimestamp() >= $now) && ($this->since->getTimestamp() <= $now);
    }
}
