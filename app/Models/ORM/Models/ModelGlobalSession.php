<?php

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\AbstractModel;
use Nette\Database\Table\ActiveRow;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read \DateTimeInterface until
 * @property-read \DateTimeInterface since
 * @property-read int login_id
 * @property-read ActiveRow login
 * @property-read string session_id
 * @property-read string remote_ip
 */
class ModelGlobalSession extends AbstractModel {

    public function isValid(): bool {
        $now = time();
        return ($this->until->getTimestamp() >= $now) && ($this->since->getTimestamp() <= $now);
    }

    public function getLogin(): ModelLogin {
        return ModelLogin::createFromActiveRow($this->login);
    }
}
