<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest\Tests\Person;

use FKSDB\Components\DataTest\Test;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\EventOrganizerModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\OrganizerModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<PersonModel>
 */
class EventCoveringTest extends Test
{
    public function getTitle(): Title
    {
        return new Title(null, _('Organization and participation at same year'));
    }

    /**
     * @param PersonModel $model
     */
    public function run(Logger $logger, Model $model): void
    {
        $contestantYears = [
            ContestModel::ID_FYKOS => [],
            ContestModel::ID_VYFUK => [],
        ];
        $participantsYears = [
            ContestModel::ID_FYKOS => [],
            ContestModel::ID_VYFUK => [],
        ];
        /** @var EventParticipantModel $eventParticipant */
        foreach ($model->getEventParticipants() as $eventParticipant) {
            $year = $eventParticipant->event->year;
            $contestId = $eventParticipant->event->event_type->contest_id;
            if (!\in_array($year, $participantsYears[$contestId])) {
                $participantsYears[$contestId][] = $year;
            }
        }
        /** @var ContestantModel $contestant */
        foreach ($model->getContestants() as $contestant) {
            $year = $contestant->year;
            $contestId = $contestant->contest_id;
            if (!\in_array($year, $contestantYears[$contestId])) {
                $contestantYears[$contestId][] = $year;
            }
        }
        $eventOrganizerYears = $this->getEventOrganizerYears($model);

        $this->check($logger, $participantsYears, $eventOrganizerYears, 'eventParticipant', $model);
        $this->check($logger, $contestantYears, $eventOrganizerYears, 'contestant', $model);
    }

    /** @phpstan-ignore-next-line */
    private function check(Logger $logger, array $data, array $organizers, string $type, PersonModel $person): void
    {
        foreach ($data as $contestId => $contestYears) {
            foreach ($contestYears as $year) {
                if (\in_array($year, $organizers[$contestId])) {
                    $logger->log($this->createLog($year, $contestId, $type, 'eventOrganizer'));
                }
                $query = $person->getLegacyOrganizers($contestId);
                /** @var OrganizerModel $organizer */
                foreach ($query as $organizer) {
                    if ($organizer->until) {
                        if ($organizer->until >= $year && $organizer->since <= $year) {
                            $logger->log($this->createLog($year, $contestId, $type, 'organizer'));
                        }
                    } elseif ($organizer->since <= $year) {
                        $logger->log($this->createLog($year, $contestId, $type, 'organizer'));
                    }
                }
            }
        }
    }

    private function createLog(int $year, int $contestId, string $typeP, string $typeO): Message
    {
        return new Message(
            \sprintf(
                _('Organization and participation at same year %d and contestId %d %s<->%s.'),
                $year,
                $contestId,
                $typeP,
                $typeO
            ),
            Message::LVL_ERROR
        );
    }

    /**
     * @phpstan-return array<int,array<int,int>>
     */
    private function getEventOrganizerYears(PersonModel $person): array
    {
        $eventOrganizerYears = [
            ContestModel::ID_FYKOS => [],
            ContestModel::ID_VYFUK => [],
        ];
        /** @var EventOrganizerModel $eventOrganizer */
        foreach ($person->getEventOrganizers() as $eventOrganizer) {
            $year = $eventOrganizer->event->year;
            $contestId = $eventOrganizer->event->event_type->contest_id;
            if (!\in_array($year, $eventOrganizerYears[$contestId])) {
                $eventOrganizerYears[$contestId][] = $year;
            }
        }
        return $eventOrganizerYears;
    }
}
