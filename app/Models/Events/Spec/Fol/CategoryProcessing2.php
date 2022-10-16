<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Spec\Fol;

use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\Events\Spec\AbstractCategoryProcessing;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use Nette\Utils\ArrayHash;

class CategoryProcessing2 extends AbstractCategoryProcessing
{
    /**
     *   Open (staří odkudkoliv - pokazí to i jeden člen týmu)
     *   Zahraniční
     *   ČR - A - (3,4]
     *   ČR - B - (2,3] - max. 2 ze 4. ročníku
     *   ČR - C - [0,2] - nikdo ze 4. ročníku, max. 2 z 3 ročníku
     */
    protected function getCategory(Holder $holder, ArrayHash $values): ?TeamCategory
    {
        $members = $this->extractValues($holder);
        // init stats
        $olds = 0;
        $years = [0, 0, 0, 0, 0]; //0 - ZŠ, 1..4 - SŠ
        // calculate stats
        foreach ($members as $member) {
            if (!$member['school_id']) { // for future
                $olds += 1;
            }

            if ($member['study_year'] === null) {
                $olds += 1;
            } elseif ($member['study_year'] >= 1 && $member['study_year'] <= 4) {
                $years[(int)$member['study_year']] += 1;
            } else {
                $years[0] += 1; // ZŠ
            }
        }
        // evaluate stats
        if ($olds > 0) {
            return TeamCategory::tryFrom(TeamCategory::O);
        } else {
            $sum = 0;
            $cnt = 0;
            foreach ($years as $studyYear => $count) {
                $sum += $count * $studyYear;
                $cnt += $count;
            }
            $avg = $sum / $cnt;
            if ($avg <= 2 && $years[4] == 0 && $years[3] <= 2) {
                return TeamCategory::tryFrom(TeamCategory::C);
            } elseif ($avg <= 3 && $years[4] <= 2) {
                return TeamCategory::tryFrom(TeamCategory::B);
            } else {
                return TeamCategory::tryFrom(TeamCategory::A);
            }
        }
    }
}
