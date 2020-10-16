<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Utils\Strings;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read int $contest_id
 * @property-read string $name
 */
class ModelContest extends AbstractModelSingle {
    public const ID_FYKOS = 1;
    public const ID_VYFUK = 2;

    public function getContestSymbol(): string {
        return strtolower(Strings::webalize($this->name));
    }
}
