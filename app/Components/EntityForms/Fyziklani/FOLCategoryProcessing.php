<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\StudyYear;
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
        $year = [
            'P' => 0,
            StudyYear::H_1 => 0,
            StudyYear::H_2 => 0,
            StudyYear::H_3 => 0,
            StudyYear::H_4 => 0,
        ]; //0 - ZŠ, 1..4 - SŠ
        // calculate stats
        foreach ($members as $member) {
            $history = $member->getHistoryByContestYear($event->getContestYear());
            if (!$history->school) { // for future
                $olds += 1;
            }
            $studyYear = StudyYear::tryFromLegacy($history->study_year);
            if (
                is_null($studyYear) || $studyYear->value === StudyYear::NONE || $studyYear->value === StudyYear::U_ALL
            ) {
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
            for ($y = 1; $y <= 4; ++$y) {
                $sum += $year['H_' . $y] * $y;
                $cnt += $year['H_' . $y] ?? 0;
            }
            $avg = $sum / $cnt;
            if ($avg <= 2 && $year[StudyYear::H_4] === 0 && $year[StudyYear::H_3] <= 2) {
                return TeamCategory::tryFrom(TeamCategory::C);
            } elseif ($avg <= 3 && $year[StudyYear::H_4] <= 2) {
                return TeamCategory::tryFrom(TeamCategory::B);
            } else {
                return TeamCategory::tryFrom(TeamCategory::A);
            }
        }
    }
}
