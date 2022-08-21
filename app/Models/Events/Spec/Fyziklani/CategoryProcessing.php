<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Spec\Fyziklani;

use FKSDB\Models\Events\Exceptions\SubmitProcessingException;
use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\Events\Spec\AbstractCategoryProcessing;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use Nette\Utils\ArrayHash;

/**
 * Na Fyziklani 2013 jsme se rozhodli pocitat tymum automaticky kategorii ve ktere soutezi podle pravidel.
 */
class CategoryProcessing extends AbstractCategoryProcessing
{

    protected function getCategory(Holder $holder, ArrayHash $values): ?TeamCategory
    {
        if ($values['team']['force_a']) {
            return TeamCategory::tryFrom(TeamCategory::A);
        }
        $coefficientSum = 0;
        $count4 = 0;
        $count3 = 0;

        $members = $this->extractValues($holder);
        foreach ($members as $member) {
            $studyYear = $member['study_year'];
            $coefficient = ($studyYear >= 1 && $studyYear <= 4) ? $studyYear : 0;
            $coefficientSum += $coefficient;

            if ($coefficient == 4) {
                $count4++;
            } elseif ($coefficient == 3) {
                $count3++;
            }
        }

        $categoryHandle = $members ? ($coefficientSum / count($members)) : 999;

        // if ($abroad > 0) {
        //     $result = 'F';
        // } else
        if ($categoryHandle <= 2 && $count4 == 0 && $count3 <= 2) {
            $result = TeamCategory::tryFrom(TeamCategory::C);
        } elseif ($categoryHandle <= 3 && $count4 <= 2) {
            $result = TeamCategory::tryFrom(TeamCategory::B);
        } elseif ($categoryHandle <= 4) {
            $result = TeamCategory::tryFrom(TeamCategory::A);
        } else {
            throw new SubmitProcessingException(_('Cannot determine category.'));
            //$result = ModelFyziklaniTeam::CATEGORY_HIGH_SCHOOL_A; // TODO hack if all study year fields are disabled
        }
        return $result;
    }
}
