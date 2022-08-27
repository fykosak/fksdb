<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Spec\Fol;

use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\Events\Spec\AbstractCategoryProcessing;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\ORM\Services\SchoolService;
use Nette\InvalidArgumentException;
use Nette\Utils\ArrayHash;

class CategoryProcessing extends AbstractCategoryProcessing
{

    private int $rulesVersion;
    protected SchoolService $schoolService;

    public function __construct(int $rulesVersion, SchoolService $schoolService, PersonService $personService)
    {
        parent::__construct($personService);
        $this->schoolService = $schoolService;
        if (!in_array($rulesVersion, [1, 2])) {
            throw new InvalidArgumentException(_('Not valid $rulesVersion.'));
        }
        $this->rulesVersion = $rulesVersion;
    }

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
        $year = [0, 0, 0, 0, 0]; //0 - ZŠ, 1..4 - SŠ
        $abroad = 0;
        // calculate stats
        foreach ($members as $member) {
            if (!$member['school_id']) { // for future
                $olds += 1;
            } else {
                $school = $this->schoolService->findByPrimary($member['school_id']);
                if (!in_array($school->address->region->country_iso, ['CZ', 'SK'])) {
                    $abroad += 1;
                }
            }

            if ($member['study_year'] === null) {
                $olds += 1;
            } elseif ($member['study_year'] >= 1 && $member['study_year'] <= 4) {
                $year[(int)$member['study_year']] += 1;
            } else {
                $year[0] += 1; // ZŠ
            }
        }
        // evaluate stats
        if ($olds > 0) {
            return TeamCategory::tryFrom(TeamCategory::O);
        } elseif ($this->rulesVersion == 1 && $abroad > 0) {
            return TeamCategory::tryFrom(TeamCategory::F);
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
