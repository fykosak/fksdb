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
    /**
     * @param array{team:array{category:string,force_a:bool,name:string}} $values
     * @phpstan-return array{team:array{category:string,force_a:bool,name:string}}
     */
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
        [$olds, $years] = FOFCategoryProcessing::getTeamMembersYears($members, $event);
        // evaluate stats
        if ($olds > 0) {
            return TeamCategory::from(TeamCategory::O);
        } else {
            $avg = FOFCategoryProcessing::getCoefficientAvg($members, $event);
            if ($avg <= 2 && $years[StudyYear::H_4] === 0 && $years[StudyYear::H_3] <= 2) {
                return TeamCategory::from(TeamCategory::C);
            } elseif ($avg <= 3 && $years[StudyYear::H_4] <= 2) {
                return TeamCategory::from(TeamCategory::B);
            } else {
                return TeamCategory::from(TeamCategory::A);
            }
        }
    }
}
