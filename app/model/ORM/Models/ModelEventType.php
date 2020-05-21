<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use Nette\Database\Table\ActiveRow;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read ActiveRow contest
 * @property-read int contest_id
 * @property-read int event_type_id
 */
class ModelEventType extends AbstractModelSingle implements IContestReferencedModel {

    const FYZIKLANI = 1;

    public function getContest(): ModelContest {
        return ModelContest::createFromActiveRow($this->contest);
    }
}
