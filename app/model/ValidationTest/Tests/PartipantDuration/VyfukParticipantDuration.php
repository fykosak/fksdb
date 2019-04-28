<?php


namespace FKSDB\ValidationTest\Tests\ParticipantDuration;

use FKSDB\ORM\Models\ModelContest;

/**
 * Class VyfukParticipantDuration
 * @package FKSDB\ValidationTest\Tests\ParticipantDuration
 */
class VyfukParticipantDuration extends ParticipantsDuration {
    /**
     * @var ModelContest
     */
    private $contest;

    /**
     * @return string
     */
    public function getAction(): string {
        return 'participants_duration_vyfuk';
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Participate on Výfuk events');
    }

    /**
     * @return ModelContest
     */
    protected function getContest(): ModelContest {
        if (!$this->contest) {
            $row = $this->serviceContest->findByPrimary(2);
            $this->contest = ModelContest::createFromActiveRow($row);
        }
        return $this->contest;
    }
}
