<?php

namespace FKSDB\DataTesting\Tests\Person;

use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\DataTesting\TestsLogger;
use FKSDB\DataTesting\TestLog;

/**
 * Class ParticipantsDurationTest
 * @package FKSDB\DataTesting\Tests\Person
 */
class ParticipantsDurationTest extends PersonTest {

    /**
     * @param TestsLogger $logger
     * @param ModelPerson $person
     * @return void
     */
    public function run(TestsLogger $logger, ModelPerson $person) {
        $contestsDefs = [
            ModelContest::ID_FYKOS => ['thresholds' => [4, 6]],
            ModelContest::ID_VYFUK => ['thresholds' => [4, 6]]
        ];

        foreach ($contestsDefs as $contestId => $contestsDef) {
            $max = null;
            $min = null;
            foreach ($person->getEventParticipant() as $row) {
                $model = ModelEventParticipant::createFromActiveRow($row);
                $event = $model->getEvent();
                if ($event->getEventType()->contest_id !== $contestId) {
                    continue;
                }
                $year = $event->year;

                $max = (is_null($max) || $max < $year) ? $year : $max;
                $min = (is_null($min) || $min > $year) ? $year : $min;
            }

            $delta = ($max - $min) + 1;
            $logger->log(new TestLog(
                $this->getTitle(),
                \sprintf('Person participate %d years in the events of contestId %d', $delta, $contestId),
                $this->evaluateThresholds($delta, $contestsDef['thresholds'])
            ));
        }

    }

    /**
     * @param int $delta
     * @param array $thresholds
     * @return string
     */
    private final function evaluateThresholds(int $delta, array $thresholds): string {
        if ($delta < $thresholds[0]) {
            return TestLog::LVL_SUCCESS;
        }
        if ($delta < $thresholds[1]) {
            return TestLog::LVL_WARNING;
        }
        return TestLog::LVL_DANGER;
    }

    /**
     * @return string
     */
    public function getAction(): string {
        return 'participants_duration';
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Participate events');
    }
}
