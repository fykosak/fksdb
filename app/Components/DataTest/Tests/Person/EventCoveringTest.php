<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest\Tests\Person;

use FKSDB\Components\DataTest\Test;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\EventOrganizerModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\OrganizerModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;
use Tracy\Debugger;

/**
 * @phpstan-extends Test<PersonModel>
 * @phpstan-type TContestYears array<int,array<int,int>>
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
        $organizers = [
            'event organizer' => self::getEventOrganizer($model),
            'organizer' => self::getOrganizer($model),
        ];

        $participants = [
            'event participant' => self::getEventParticipant($model),
            'team member' => self::getTeamMember($model),
            'contestant' => self::getContestant($model),
        ];
        foreach ($participants as $participantKey => $participant) {
            foreach ($organizers as $organizerKey => $organizer) {
                $this->check($logger, $participantKey, $participant, $organizerKey, $organizer);
            }
        }
    }

    /**
     * @phpstan-param TContestYears $participants
     * @phpstan-param TContestYears $organizers
     */
    private function check(
        Logger $logger,
        string $participantKey,
        array $participants,
        string $organizersKey,
        array $organizers
    ): void {
        Debugger::barDump($participants);
        Debugger::barDump($organizers);
        foreach ($participants as $contestId => $years) {
            foreach ($years as $year) {
                if (isset($organizers[$contestId]) && \in_array($year, $organizers[$contestId])) {
                    $logger->log(self::createLog($year, $contestId, $participantKey, $organizersKey));
                }
            }
        }
    }


    private static function createLog(int $year, int $contestId, string $participantKey, string $organizersKey): Message
    {
        return new Message(
            \sprintf(
                _('Organization and participation at %d year of contestId %d "%s"<->"%s".'),
                $year,
                $contestId,
                _($participantKey),
                _($organizersKey)
            ),
            Message::LVL_ERROR
        );
    }

    /**
     * @phpstan-param ContestYearModel[] $contestYears
     * @phpstan-return TContestYears
     */
    private static function contestYearsToArray(array $contestYears): array
    {
        $data = [];
        foreach ($contestYears as $contestYear) {
            $data[$contestYear->contest_id] = $data[$contestYear->contest_id] ?? [];
            $data[$contestYear->contest_id][$contestYear->year] = $contestYear->year;
        }
        return $data;
    }

    /**
     * @phpstan-return TContestYears
     */
    private function getEventOrganizer(PersonModel $person): array
    {
        $contestYears = [];
        /** @var EventOrganizerModel $eventOrganizer */
        foreach ($person->getEventOrganizers() as $eventOrganizer) {
            $contestYears[] = $eventOrganizer->event->getContestYear();
        }
        return self::contestYearsToArray($contestYears);
    }

    /**
     * @phpstan-return TContestYears
     */
    private static function getOrganizer(PersonModel $person): array
    {
        $contestYears = [];
        /** @var OrganizerModel $organizer */
        foreach ($person->getOrganizers() as $organizer) {
            $since = $organizer->since;
            $until = $organizer->until ?? $organizer->contest->getLastYear();
            $contestYears[$organizer->contest_id] = [];
            foreach (range($since, $until) as $year) {
                $contestYears[$organizer->contest_id][$year] = $year;
            }
        }
        return $contestYears;
    }

    /**
     * @phpstan-return TContestYears
     */
    private static function getTeamMember(PersonModel $model): array
    {
        $contestYears = [];
        /** @var TeamMemberModel $teamMember */
        foreach ($model->getTeamMembers() as $teamMember) {
            $contestYears[] = $teamMember->fyziklani_team->event->getContestYear();
        }
        return self::contestYearsToArray($contestYears);
    }

    /**
     * @phpstan-return TContestYears
     */
    private static function getEventParticipant(PersonModel $model): array
    {
        $contestYears = [];
        /** @var EventParticipantModel $eventParticipant */
        foreach ($model->getEventParticipants() as $eventParticipant) {
            $contestYears[] = $eventParticipant->event->getContestYear();
        }
        return self::contestYearsToArray($contestYears);
    }

    /**
     * @phpstan-return TContestYears
     */
    private static function getContestant(PersonModel $model): array
    {
        $contestYears = [];
        /** @var ContestantModel $contestant */
        foreach ($model->getContestants() as $contestant) {
            $contestYears[] = $contestant->getContestYear();
        }
        return self::contestYearsToArray($contestYears);
    }
}
