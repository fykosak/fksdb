<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property integer contest_id
 */
class ModelContest extends AbstractModelSingle {
    const ID_FYKOS = 1;
    const ID_VYFUK = 2;

    public function getContestSymbol() {
        switch ($this->contest_id) {
            case 1:
                return 'fykos';
            case 2:
                return 'vyfuk';
        };
        return null;
    }
}
