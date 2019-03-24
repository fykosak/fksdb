<?php


namespace FKSDB\ValidationTest\Tests\ParticipantDuration;


use FKSDB\ORM\Models\ModelContest;

/**
 * Class FykosParticipantDuration
 * @package FKSDB\ValidationTest\Tests\ParticipantDuration
 */
class FykosParticipantDuration extends ParticipantsDuration {

    /**
     * @return string
     */
    public static function getAction(): string {
        return 'participants_duration_fykos';
    }

    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Participate on FYKOS events');
    }

    /**
     * @return ModelContest
     */
    protected function getContest(): ModelContest {
        $row = $this->serviceContest->findByPrimary(1);
        return ModelContest::createFromTableRow($row);
    }
}
