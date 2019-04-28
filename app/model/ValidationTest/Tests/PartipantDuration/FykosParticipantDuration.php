<?php


namespace FKSDB\ValidationTest\Tests\ParticipantDuration;


use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Database\Table\Selection;

/**
 * Class FykosParticipantDuration
 * @package FKSDB\ValidationTest\Tests\ParticipantDuration
 */
class FykosParticipantDuration extends ParticipantsDuration {

    /**
     * @return string
     */
    public function getAction(): string {
        return 'participants_duration_fykos';
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Participate on FYKOS events');
    }

    /**
     * @return ModelContest
     */
    protected function getContest(): ModelContest {
        static $model;
        if ($model) {
            return $model;
        }
        $row = $this->serviceContest->findByPrimary(1);
        $model = ModelContest::createFromActiveRow($row);
        return $model;
    }

    /**
     * @param ModelPerson $person
     * @return Selection
     */
    protected function getEventParticipant(ModelPerson $person): Selection {
        return parent::getEventParticipant($person)->where('event.event_type_id !=', 9);
    }
}
