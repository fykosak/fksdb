<?php

namespace FKSDB\Models\DataTesting\Tests\ModelPerson;

use Fykosak\Utils\Logging\Logger;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\DataTesting\TestLog;
use Fykosak\Utils\Logging\Message;

class ParticipantsDurationTest extends PersonTest {

    private const CONTESTS = [
        ModelContest::ID_FYKOS => ['thresholds' => [4, 6]],
        ModelContest::ID_VYFUK => ['thresholds' => [4, 6]],
    ];

    public function __construct() {
        parent::__construct('participants_duration', _('Participate events'));
    }

    public function run(Logger $logger, ModelPerson $person): void {
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
                $this->title,
                \sprintf('Person participate %d years in the events of contestId %d', $delta, $contestId),
                $this->evaluateThresholds($delta, $contestDef['thresholds'])
            ));
        }
    }

    final private function evaluateThresholds(int $delta, array $thresholds): string {
        if ($delta < $thresholds[0]) {
            return Message::LVL_SUCCESS;
        }
        if ($delta < $thresholds[1]) {
            return Message::LVL_WARNING;
        }
        return Message::LVL_ERROR;
    }
}
