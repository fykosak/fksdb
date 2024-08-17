<?php

declare(strict_types=1);

namespace FKSDB\Components\Applications\Team;

use FKSDB\Components\Controls\ColumnPrinter\ColumnRendererComponent;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Tests\Event\Team\TeamsPerSchool;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\DI\Container;

class SchoolCheckComponent extends BaseComponent
{
    private TeamModel2 $team;

    public function __construct(TeamModel2 $team, Container $container)
    {
        parent::__construct($container);
        $this->team = $team;
    }

    final public function render(): void
    {
        $schools = [];
        foreach (TeamsPerSchool::getSchoolsFromTeam($this->team) as $school) {
            $schools[$school->school_id] = [
                'school' => $school,
            ];
            $query = TeamsPerSchool::getTeamsFromSchool($school, $this->team->event);
            foreach ($query as $team) {
                $schools[$school->school_id][] = $team;
            }
        }
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.schoolCheck.latte', [
            'schools' => $schools,
        ]);
    }

    protected function createComponentValuePrinter(): ColumnRendererComponent
    {
        return new ColumnRendererComponent($this->getContext());
    }
}
