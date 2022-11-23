<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Models\PersonModel;
use Nette\Forms\Form;

class FOLCategoryProcessing extends FormProcessing
{

    public function __invoke(array $values, Form $form, EventModel $event): array
    {
        $members = TeamFormComponent::getMembersFromForm($form);
        $values['team']['category'] = $this->getCategory($members, $event)->value;
        return $values;
    }

    /**
     *   Open (staří odkudkoliv - pokazí to i jeden člen týmu)
     *   Zahraniční
     *   ČR - A - (3,4]
     *   ČR - B - (2,3] - max. 2 ze 4. ročníku
     *   ČR - C - [0,2] - nikdo ze 4. ročníku, max. 2 z 3 ročníku
     * @param PersonModel[] $members
     */
    protected function getCategory(array $members, EventModel $event): TeamCategory
    {
        if (!count($members)) {
            throw new NoMemberException();
        }
        // init stats
        $olds = 0;
        $year = [0, 0, 0, 0, 0]; //0 - ZŠ, 1..4 - SŠ
        // calculate stats
        foreach ($members as $member) {
            $history = $member->getHistory($event->getContestYear()->ac_year);
            if (!$history->school) { // for future
                $olds += 1;
            }

            if (is_null($history->study_year)) {
                $olds += 1;
            } elseif ($history->study_year >= 1 && $history->study_year <= 4) {
                $year[(int)$history->study_year] += 1;
            } else {
                $year[0] += 1; // ZŠ
            }
        }
        // evaluate stats
        if ($olds > 0) {
            return TeamCategory::tryFrom(TeamCategory::O);
        } else { //Czech/Slovak highschoolers (or lower)
            $sum = 0;
            $cnt = 0;
            for ($y = 0; $y <= 4; ++$y) {
                $sum += $year[$y] * $y;
                $cnt += $year[$y];
            }
            $avg = $sum / $cnt;
            if ($avg <= 2 && $year[4] == 0 && $year[3] <= 2) {
                return TeamCategory::tryFrom(TeamCategory::C);
            } elseif ($avg <= 3 && $year[4] <= 2) {
                return TeamCategory::tryFrom(TeamCategory::B);
            } else {
                return TeamCategory::tryFrom(TeamCategory::A);
            }
        }
    }
}
