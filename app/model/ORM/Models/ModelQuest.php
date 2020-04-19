<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;

/**
 * 
 * @author Miroslav Jarý <mira.jary@gmail.com>
 *
 */
class ModelQuest extends AbstractModelSingle implements IContestReferencedModel {
    /**
     * 
     * {@inheritDoc}
     * @see \FKSDB\ORM\Models\IContestReferencedModel::getContest()
     */
    public function getContest(): ModelContest {
        return ModelContest::createFromActiveRow($this->ref(DbNames::TAB_CONTEST, 'contest_id'));
    }
}
