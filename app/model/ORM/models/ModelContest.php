<?php

use Nette\Utils\Strings;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property $contest_id integer
 * @property $name string
 */
class ModelContest extends AbstractModelSingle {
    const ID_FYKOS = 1;
    const ID_VYFUK = 2;

    public function getContestSymbol() {
        return strtolower(Strings::webalize($this->name));
    }
}
