<?php

declare(strict_types=1);

namespace FKSDB\Models\DataTesting\Tests\Person;

use FKSDB\Models\DataTesting\Test;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;

/**
 * @phpstan-extends Test<PersonModel>
 */
class ParticipantsDurationTest extends Test
{

    private const CONTESTS = [
        ContestModel::ID_FYKOS => ['thresholds' => [5, 6]],
        ContestModel::ID_VYFUK => ['thresholds' => [5, 6]],
    ];

    public function __construct()
    {
        parent::__construct(_('Participate events'));
    }

    /**
     * @param PersonModel $person
     */
    public function run(Logger $logger, Model $person): void
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
                new Message(
                    \sprintf(_('Person participate %d years in the events of contestId %d'), $delta, $contestId),
                    $this->evaluateThresholds($delta, $contestDef['thresholds'])
                )
            );
        }
    }

    /**
     * @phpstan-param array{int,int} $thresholds
     */
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
