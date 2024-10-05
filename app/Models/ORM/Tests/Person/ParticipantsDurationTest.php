<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Person;

use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Components\DataTest\TestMessage;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Localization\LangMap;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<PersonModel>
 * @phpstan-type TContestYears array<int,array<int,ContestYearModel>>
 */
final class ParticipantsDurationTest extends Test
{

    private const CONTESTS = [
        ContestModel::ID_FYKOS => [5, 6],
        ContestModel::ID_VYFUK => [5, 6],
    ];

    public function getTitle(): Title
    {
        return new Title(null, _('Participation duration'));
    }

    public function getDescription(): ?LangMap
    {
        return new LangMap([
            'en' => 'Check how long person participated in events of the contest.',
            'cs' => '',
        ]);
    }

    /**
     * @param PersonModel $model
     */
    protected function innerRun(TestLogger $logger, Model $model, string $id): void
    {
        $data = [
            'event participant' => self::getEventParticipant($model),
            'team member' => self::getTeamMember($model),
            'contestant' => self::getContestant($model),
        ];
        foreach (self::CONTESTS as $contestId => $thresholds) {
            $max = null;
            $min = null;
            foreach ($data as $datum) {
                if (isset($datum[$contestId])) {
                    $localMax = max(array_keys($datum[$contestId]));
                    $localMin = min(array_keys($datum[$contestId]));

                    $max = (is_null($max) || $max < $localMax) ? $localMax : $max;
                    $min = (is_null($min) || $min > $localMin) ? $localMin : $min;
                }
            }

            $delta = ($max - $min) + 1;
            if ($delta < $thresholds[0]) {
                continue;
            }
            if ($delta < $thresholds[1]) {
                $level = Message::LVL_WARNING;
            } else {
                $level = Message::LVL_ERROR;
            }
            $logger->log(
                new TestMessage(
                    $id,
                    \sprintf(_('Person participated %d years in contestId %d (and its events)'), $delta, $contestId),
                    $level
                )
            );
        }
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
        return 'personParticipantsDuration';
    }

    /**
     * @phpstan-return TContestYears
     */
    public static function getTeamMember(PersonModel $model): array
    {
        $contestYears = [];
        /** @var TeamMemberModel $teamMember */
        foreach ($model->getTeamMembers() as $teamMember) {
            $event = $teamMember->fyziklani_team->event;
            if ($event->event_type_id === 9) {
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
}
