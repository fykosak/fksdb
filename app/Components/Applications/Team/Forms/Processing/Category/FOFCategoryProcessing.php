<?php

declare(strict_types=1);

namespace FKSDB\Components\Applications\Team\Forms\Processing\Category;

use FKSDB\Components\Applications\Team\Forms\NoMemberException;
use FKSDB\Components\Applications\Team\Forms\TeamForm;
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
final class FOFCategoryProcessing extends Preprocessing
{
    private EventModel $event;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    /**
     * @throws OldMemberException
     */
    public function __invoke(array $values, Form $form, ?Model $model): array
    {
        $members = TeamForm::getFormMembers($form);
        $values['team']['category'] = $this->getCategory($members)->value;
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
            $history = $member->getHistory($event->getContestYear());
            $studyYear = $history->study_year_new;
            switch ($studyYear->value) {
                case StudyYear::None:
                case StudyYear::UniversityAll:
                    $olds += 1;
                    break;
                case StudyYear::High1:
                case StudyYear::High2:
                case StudyYear::High3:
                case StudyYear::High4:
                    $years[$studyYear->value] += 1;
                    break;
                default:
                    $years['P'] += 1;
                    break;
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
     */
    protected function getCategory(array $members): TeamCategory
    {
        [$olds, $years] = self::getTeamMembersYears($members, $this->event);
        if ($olds > 0) {
            throw new OldMemberException();
        }
        $avg = $this->getCoefficientAvg($members, $this->event);
        if ($avg <= 2 && $years[StudyYear::High4] == 0 && $years[StudyYear::High3] <= 2) {
            return TeamCategory::C;
        } elseif ($avg <= 3 && $years[StudyYear::High4] <= 2) {
            return TeamCategory::B;
        } else {
            return TeamCategory::A;
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

    /**
     * @throws OldMemberException
     */
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
