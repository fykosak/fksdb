<?php

namespace FKSDB\ORM\Models;

use AbstractModelSingle;
use Nette\Utils\Strings;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property int $contest_id
 * @property string $name
 */
class ModelContest extends AbstractModelSingle {
    const ID_FYKOS = 1;
    const ID_VYFUK = 2;

    /**
     * @return string
     */
    public function getContestSymbol(): string {
        return strtolower(Strings::webalize($this->name));
    }
}
