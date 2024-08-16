<?php

declare(strict_types=1);

namespace FKSDB\Components\Application\Team\Processing\Category;

use FKSDB\Components\Application\Team\TeamForm;
use FKSDB\Components\EntityForms\Processing\Preprocessing;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\StudyYear;
use Fykosak\NetteORM\Model\Model;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-extends Preprocessing<TeamModel2,array{team:array{category:string,name:string}}>
 */
final class FOLCategoryProcessing extends Preprocessing
{
    private EventModel $event;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    public function __invoke(array $values, Form $form, ?Model $model): array
    {
        $members = TeamForm::getFormMembers($form);
        $values['team']['category'] = $this->getCategory($members)->value;
        return $values;
    }

    /**
     *   Open (staří odkudkoliv - pokazí to i jeden člen týmu)
     *   Zahraniční
     *   ČR - A - (3,4]
     *   ČR - B - (2,3] - max. 2 ze 4. ročníku
     *   ČR - C - [0,2] - nikdo ze 4. ročníku, max. 2 z 3 ročníku
     * @phpstan-param PersonModel[] $members
     */
    protected function getCategory(array $members): TeamCategory
    {
        [$olds, $years] = FOFCategoryProcessing::getTeamMembersYears($members, $this->event);
        // evaluate stats
        if ($olds > 0) {
            return TeamCategory::from(TeamCategory::O);
        } else {
            $avg = FOFCategoryProcessing::getCoefficientAvg($members, $this->event);
            if ($avg <= 2 && $years[StudyYear::High4] === 0 && $years[StudyYear::High3] <= 2) {
                return TeamCategory::from(TeamCategory::C);
            } elseif ($avg <= 3 && $years[StudyYear::High4] <= 2) {
                return TeamCategory::from(TeamCategory::B);
            } else {
                return TeamCategory::from(TeamCategory::A);
            }
        }
    }

    public function test(TeamModel2 $team): TeamCategory
    {
        $members = [];
        /** @var TeamMemberModel $member */
        foreach ($team->getMembers() as $member) {
            $members[] = $member->person;
        }
        return $this->getCategory($members);
    }
}
