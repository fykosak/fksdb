<?php

declare(strict_types=1);

namespace FKSDB\Models\DataTesting\Tests\ModelPerson;

use FKSDB\Models\DataTesting\TestLog;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;

class ParticipantsDurationTest extends PersonTest
{

    private const CONTESTS = [
        ContestModel::ID_FYKOS => ['thresholds' => [5, 6]],
        ContestModel::ID_VYFUK => ['thresholds' => [5, 6]],
    ];

    public function __construct()
    {
        parent::__construct('participants_duration', _('Participate events'));
    }

    public function run(Logger $logger, PersonModel $person): void
    {
        foreach (self::CONTESTS as $contestId => $contestDef) {
            $max = null;
            $min = null;
            /** @var EventParticipantModel $model */
            foreach ($person->getEventParticipants() as $model) {
                $event = $model->event;
                if ($event->event_type->contest_id !== $contestId) {
                    continue;
                }
                $year = $event->year;

                $max = (is_null($max) || $max < $year) ? $year : $max;
                $min = (is_null($min) || $min > $year) ? $year : $min;
            }

            $delta = ($max - $min) + 1;
            $logger->log(
                new TestLog(
                    $this->title,
                    \sprintf('Person participate %d years in the events of contestId %d', $delta, $contestId),
                    $this->evaluateThresholds($delta, $contestDef['thresholds'])
                )
            );
        }
    }

    private function evaluateThresholds(int $delta, array $thresholds): string
    {
        if ($delta < $thresholds[0]) {
            return Message::LVL_SUCCESS;
        }
        if ($delta < $thresholds[1]) {
            return Message::LVL_WARNING;
        }
        return Message::LVL_ERROR;
    }
}
