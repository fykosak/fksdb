<?php

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\AbstractModel;
use Nette\Database\Table\ActiveRow;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read int contest_id
 * @property-read int ac_year
 * @property-read int year
 * @property-read ActiveRow contest
 */
class ModelContestYear extends AbstractModel {

    public function getContest(): ModelContest {
        return ModelContest::createFromActiveRow($this->contest);
    }

}
