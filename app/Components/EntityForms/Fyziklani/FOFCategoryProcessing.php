<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\StudyYear;
use Nette\Forms\Form;

class FOFCategoryProcessing extends FormProcessing
{
    /**
     * @phpstan-param array{team:array{category:string,force_a:bool,name:string}} $values
     * @phpstan-return array{team:array{category:string,force_a:bool,name:string}}
     */
    public function __invoke(array $values, Form $form, EventModel $event): array
    {
        $members = TeamFormComponent::getMembersFromForm($form);
        $values['team']['category'] = $this->getCategory($members, $event, $values)->value;
        return $values;
    }

    /**
     * @phpstan-param PersonModel[] $members
     * @phpstan-return array{int,array<string,int>}
     * @throws NoMemberException
     */
    public static function getTeamMembersYears(array $members, EventModel $event): array
    {
        if (!count($members)) {
            throw new NoMemberException();
        }
        $olds = 0;
        $years = [
            'P' => 0,
            StudyYear::High1 => 0,
            StudyYear::High2 => 0,
            StudyYear::High3 => 0,
            StudyYear::High4 => 0,
        ];

        // calculate stats
        foreach ($members as $member) {
            $history = $member->getHistoryByContestYear($event->getContestYear());
            $studyYear = $history->study_year_new;
            if (
                $studyYear->value === StudyYear::None
                || $studyYear->value === StudyYear::UniversityAll
            ) {
                $olds += 1;
            } elseif ($studyYear->isHighSchool()) {
                $years[$studyYear->value] += 1;
            } elseif ($studyYear->isPrimarySchool()) {
                $years['P'] += 1;
            }
        }

        return [$olds, $years];
    }

    /**
     *   Open (staří odkudkoliv - pokazí to i jeden člen týmu)
     *   Zahraniční
     *   ČR - A - (3,4]
     *   ČR - B - (2,3] - max. 2 ze 4. ročníku
     *   ČR - C - [0,2] - nikdo ze 4. ročníku, max. 2 z 3 ročníku
     * @phpstan-param PersonModel[] $members
     * @throws NoMemberException
     * @throws OldMemberException
     * @phpstan-param array{team:array{force_a:bool}} $values
     */
    protected function getCategory(array $members, EventModel $event, array $values): TeamCategory
    {
        [$olds, $years] = self::getTeamMembersYears($members, $event);
        if ($olds > 0) {
            throw new OldMemberException();
        }
        if ($values['team']['force_a']) {
            return TeamCategory::from(TeamCategory::A);
        }
        $avg = $this->getCoefficientAvg($members, $event);
        if ($avg <= 2 && $years[StudyYear::High4] == 0 && $years[StudyYear::High3] <= 2) {
            return TeamCategory::from(TeamCategory::C);
        } elseif ($avg <= 3 && $years[StudyYear::High4] <= 2) {
            return TeamCategory::from(TeamCategory::B);
        } else {
            return TeamCategory::from(TeamCategory::A);
        }
    }

    /**
     * @phpstan-param PersonModel[] $members
     * @throws NoMemberException
     */
    public static function getCoefficientAvg(array $members, EventModel $event): float
    {
        [, $years] = self::getTeamMembersYears($members, $event);
        $sum = 0;
        $cnt = $years['P'];
        for ($y = 1; $y <= 4; ++$y) {
            $sum += $years['H_' . $y] * $y;
            $cnt += $years['H_' . $y];
        }
        return $sum / $cnt;
    }

    public function test(TeamModel2 $team): TeamCategory
    {
        $members = [];
        /** @var TeamMemberModel $member */
        foreach ($team->getMembers() as $member) {
            $members[] = $member->person;
        }
        return $this->getCategory($members, $team->event, ['team' => ['force_a' => (bool)$team->force_a]]);
    }
}
