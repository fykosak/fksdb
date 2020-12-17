<?php

namespace FKSDB\Model\ORM\Models;

use FKSDB\ORM\DeprecatedLazyModel;
use Nette\Database\Table\ActiveRow;

/**
 *
 * @author Miroslav Jarý <mira.jary@gmail.com>
 * @property-read ActiveRow task
 * @property-read int task_id
 * @property-read int question_id
 * @property-read int question_nr
 */
class ModelQuizQuestion extends AbstractModelSingle implements IContestReferencedModel {
    use DeprecatedLazyModel;

    public function getFQName(): string {
        return sprintf(_('%s. otázka'), $this->question_nr);
    }

    public function getTask(): ModelTask {
        return ModelTask::createFromActiveRow($this->task);
    }

    public function getContest(): ModelContest {
        return $this->getTask()->getContest();
    }
}