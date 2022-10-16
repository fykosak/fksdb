<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\FormProcessing;

use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\StudyYear;
use Nette\Forms\Form;

class FOLCategoryProcessing extends FormProcessing
{
    public function __invoke(array $values, Form $form, EventModel $event): array
    {
        $members = [];
        for ($member = 0; $member < 5; $member++) {
            /** @var ReferencedId $referencedId */
            $referencedId = $form->getComponent('member_' . $member);
            $person = $referencedId->getModel();
            if ($person) {
                $members[] = $person;
            }
        }
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
        // init stats
        $olds = 0;
        $year = [
            'P' => 0,
            StudyYear::High1->value => 0,
            StudyYear::High2->value => 0,
            StudyYear::High3->value => 0,
            StudyYear::High4->value => 0,
        ]; //0 - ZŠ, 1..4 - SŠ
        // calculate stats
        foreach ($members as $member) {
            $history = $member->getHistory($event->getContestYear()->ac_year);
            if (!$history->school) { // for future
                $olds += 1;
            }
            $studyYear = StudyYear::tryFromLegacy($history->study_year);
            if (is_null($studyYear) || $studyYear === StudyYear::None || $studyYear === StudyYear::UniversityAll) {
                $olds += 1;
            } elseif ($studyYear->isHighSchool()) {
                $year[$studyYear->value] += 1;
            } elseif ($studyYear->isPrimarySchool()) {
                $year['P'] += 1;
            }
        }
        // evaluate stats
        if ($olds > 0) {
            return TeamCategory::tryFrom(TeamCategory::O);
        } else {
            $sum = 0;
            $cnt = $year['P'];
            /** @var StudyYear $value */
            foreach ([StudyYear::High1, StudyYear::High2, StudyYear::High3, StudyYear::High4] as $value) {
                $sum += $year[$value->value] * $value->numeric();
                $cnt += $year[$value->value];
            }
            $avg = $sum / $cnt;
            if ($avg <= 2 && $year[StudyYear::High4->value] === 0 && $year[StudyYear::High3->value] <= 2) {
                return TeamCategory::tryFrom(TeamCategory::C);
            } elseif ($avg <= 3 && $year[StudyYear::High4->value] <= 2) {
                return TeamCategory::tryFrom(TeamCategory::B);
            } else {
                return TeamCategory::tryFrom(TeamCategory::A);
            }
        }
    }
}
