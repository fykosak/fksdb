<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;

/**
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 * @property-read int flag_id
 * @property-read int|null contest_id
 * @property-read ActiveRow contest
 */
class ModelPersonHasFlag extends AbstractModelSingle implements IContestReferencedModel {
    /**
     * @return ModelContest
     * @throws BadRequestException
     */
    public function getContest(): ModelContest {
        if ($this->contest_id) {
            return ModelContest::createFromActiveRow($this->contest);
        }
        throw new BadRequestException();
    }
}
