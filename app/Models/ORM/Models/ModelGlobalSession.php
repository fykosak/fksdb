<?php

namespace FKSDB\Models\ORM\Models;


/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read \DateTimeInterface until
 * @property-read \DateTimeInterface since
 * @property-read int login_id
 * @property-read string session_id
 * @property-read string remote_ip
 */
class ModelGlobalSession extends AbstractModelSingle {

    public function isValid(): bool {
        $now = time();
        return ($this->until->getTimestamp() >= $now) && ($this->since->getTimestamp() <= $now);
    }
}
