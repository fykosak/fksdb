<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Models\PersonModel;
use Nette\Forms\Form;

class FOFCategoryProcessing extends FormProcessing
{
    /**
     * @param array{'team':array{'category':string,'force_a':bool,'name':string}} $values
     * @phpstan-return array{'team':array{'category':string,'force_a':bool,'name':string}}
     */
    public function __invoke(array $values, Form $form, EventModel $event): array
    {
        $members = TeamFormComponent::getMembersFromForm($form);
        $values['team']['category'] = $this->getCategory($members, $event, $values)->value;
        return $values;
    }

    /**
     * @param PersonModel[] $members
     * @return int[]
     */
    protected static function getTeamMembersYears(array $members, EventModel $event): array
    {
        $years = [0, 0, 0, 0, 0]; //0 - ZŠ, 1..4 - SŠ

        if (!count($members)) {
            throw new NoMemberException();
        }

        // calculate stats
        foreach ($members as $member) {
            $history = $member->getHistory($event->getContestYear()->ac_year);
            if ($history->study_year >= 1 && $history->study_year <= 4) {
                $years[$history->study_year] += 1;
            } else {
                $years[0] += 1; // ZŠ
            }
        }

        return $years;
    }

    /**
     *   Open (staří odkudkoliv - pokazí to i jeden člen týmu)
     *   Zahraniční
     *   ČR - A - (3,4]
     *   ČR - B - (2,3] - max. 2 ze 4. ročníku
     *   ČR - C - [0,2] - nikdo ze 4. ročníku, max. 2 z 3 ročníku
     * @param PersonModel[] $members
     * @throws NoMemberException
     * @phpstan-param array{'team':array{'force_a':bool}} $values
     */
    protected function getCategory(array $members, EventModel $event, array $values): TeamCategory
    {
        $years = self::getTeamMembersYears($members, $event);
        if (!count($members)) {
            throw new NoMemberException();
        }
        if ($values['team']['force_a']) {
            return TeamCategory::tryFrom(TeamCategory::A);
        }

        $avg = $this->getCoefficientAvg($members, $event);
        if ($avg <= 2 && $years[4] == 0 && $years[3] <= 2) {
            return TeamCategory::tryFrom(TeamCategory::C);
        } elseif ($avg <= 3 && $years[4] <= 2) {
            return TeamCategory::tryFrom(TeamCategory::B);
        } else {
            return TeamCategory::tryFrom(TeamCategory::A);
        }
    }

    /**
     * @param PersonModel[] $members
     * @throws NoMemberException
     */
    public static function getCoefficientAvg(array $members, EventModel $event): float
    {
        $years = self::getTeamMembersYears($members, $event);
        $sum = 0;
        $cnt = 0;
        for ($y = 0; $y <= 4; ++$y) {
            $sum += $years[$y] * $y;
            $cnt += $years[$y];
        }
        return $sum / $cnt;
    }
}
