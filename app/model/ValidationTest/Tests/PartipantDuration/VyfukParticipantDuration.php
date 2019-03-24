<?php


namespace FKSDB\ValidationTest\Tests\ParticipantDuration;

use FKSDB\ORM\Models\ModelContest;

/**
 * Class VyfukParticipantDuration
 * @package FKSDB\ValidationTest\Tests\ParticipantDuration
 */
class VyfukParticipantDuration extends ParticipantsDuration {

    /**
     * @return string
     */
    public static function getAction(): string {
        return 'participants_duration_vyfuk';
    }

    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Participate on Výfuk events');
    }

    /**
     * @return ModelContest
     */
    protected function getContest(): ModelContest {
        $row = $this->serviceContest->findByPrimary(2);
        return ModelContest::createFromTableRow($row);
    }
}
