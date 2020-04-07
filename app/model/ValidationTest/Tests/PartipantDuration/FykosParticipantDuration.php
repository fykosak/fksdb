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
     * @var ModelContest
     */
    private $contest;

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
        if (!$this->contest) {
            $row = $this->serviceContest->findByPrimary(1);
            $this->contest = ModelContest::createFromActiveRow($row);
        }
        return $this->contest;
    }

    /**
     * @param ModelPerson $person
     * @return Selection
     */
    protected function getEventParticipant(ModelPerson $person): Selection {
        return parent::getEventParticipant($person)->where('event.event_type_id !=', 9);
    }
}
