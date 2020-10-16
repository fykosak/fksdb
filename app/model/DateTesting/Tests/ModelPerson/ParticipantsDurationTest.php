<?php

namespace FKSDB\DataTesting\Tests\Person;

use FKSDB\Logging\ILogger;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\DataTesting\TestLog;

/**
 * Class ParticipantsDurationTest
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ParticipantsDurationTest extends PersonTest {

    private const CONTESTS = [
        ModelContest::ID_FYKOS => ['thresholds' => [4, 6]],
        ModelContest::ID_VYFUK => ['thresholds' => [4, 6]],
    ];

    public function __construct() {
        parent::__construct('participants_duration', _('Participate events'));
    }

    public function run(ILogger $logger, ModelPerson $person): void {
        foreach (self::CONTESTS as $contestId => $contestDef) {
            $max = null;
            $min = null;
            foreach ($person->getEventParticipants() as $row) {
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
                $this->evaluateThresholds($delta, $contestDef['thresholds'])
            ));
        }
    }

    final private function evaluateThresholds(int $delta, array $thresholds): string {
        if ($delta < $thresholds[0]) {
            return TestLog::LVL_SUCCESS;
        }
        if ($delta < $thresholds[1]) {
            return TestLog::LVL_WARNING;
        }
        return TestLog::LVL_DANGER;
    }
}
