<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Database\Table\ActiveRow;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read int contest_id
 * @property-read ActiveRow contest
 */
class ModelGrant extends AbstractModelSingle implements IContestReferencedModel {
    /**
     * @return ModelContest
     */
    public function getContest(): ModelContest {
        return ModelContest::createFromActiveRow($this->contest);
    }
}
