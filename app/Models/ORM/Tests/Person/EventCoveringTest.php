<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Person;

use FKSDB\Models\ORM\Tests\Test;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\EventOrganizerModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\OrganizerModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<PersonModel>
 * @phpstan-type TContestYears array<int,array<int,ContestYearModel>>
 */
class EventCoveringTest extends Test
{
    public function getTitle(): Title
    {
        return new Title(null, _('Organization and participation in the same year'));
    }
    public function getDescription(): ?string
    {
        return _('Tests, if the person is participating and organizing in the same year of one contest.');
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
        foreach ($participants as $contestId => $years) {
            foreach ($years as $year => $contestYear) {
                if (isset($organizers[$contestId]) && \array_key_exists($year, $organizers[$contestId])) {
                    $logger->log(
                        new Message(
                            \sprintf(
                                _('Organization and participation in year %d (%d) of the contest %s "%s"<->"%s".'),
                                $contestYear->year,
                                $contestYear->ac_year,
                                $contestYear->contest->name,
                                _($participantKey),
                                _($organizersKey)
                            ),
                            Message::LVL_ERROR
                        )
                    );
                }
            }
        }
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
            $data[$contestYear->contest_id][$contestYear->year] = $contestYear;
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
            $contestYears[$organizer->contest_id] = [];
            foreach (range($organizer->since, $organizer->until ?? $organizer->contest->getLastYear()) as $year) {
                $contestYears[$organizer->contest_id][$year] = $organizer->contest->getContestYear($year);
            }
        }
        return $contestYears;
    }

    /**
     * @phpstan-return TContestYears
     */
    public static function getTeamMember(PersonModel $model, bool $omitFOL = false): array
    {
        $contestYears = [];
        /** @var TeamMemberModel $teamMember */
        foreach ($model->getTeamMembers() as $teamMember) {
            $event = $teamMember->fyziklani_team->event;
            if ($omitFOL && $event->event_type_id === 9) {
                continue;
            }
            $contestYears[] = $event->getContestYear();
        }
        return self::contestYearsToArray($contestYears);
    }

    /**
     * @phpstan-return TContestYears
     */
    public static function getEventParticipant(PersonModel $model): array
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
    public static function getContestant(PersonModel $model): array
    {
        $contestYears = [];
        /** @var ContestantModel $contestant */
        foreach ($model->getContestants() as $contestant) {
            $contestYears[] = $contestant->getContestYear();
        }
        return self::contestYearsToArray($contestYears);
    }

    public function getId(): string
    {
        return 'PersonEventCovering';
    }
}
