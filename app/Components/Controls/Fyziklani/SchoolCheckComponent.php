<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\Utils\BaseComponent\BaseComponent;
use FKSDB\Components\Controls\ColumnPrinter\ColumnPrinterComponent;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelSchool;
use Nette\DI\Container;

class SchoolCheckComponent extends BaseComponent
{

    private ModelEvent $event;

    public function __construct(ModelEvent $event, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    final public function render(TeamModel2 $currentTeam): void
    {
        $schools = [];
        foreach ($this->getSchoolsFromTeam($currentTeam) as $schoolId => $school) {
            $schools[$schoolId] = [
                'school' => $school,
            ];
            $query = $this->event->getFyziklaniTeams()
                ->where(
                    ':fyziklani_team_member.person:person_history.ac_year',
                    $this->event->getContestYear()->ac_year
                )
                ->where(':fyziklani_team_member.person:person_history.school_id', $schoolId);
            foreach ($query as $team) {
                $schools[$schoolId][] = TeamModel2::createFromActiveRow($team);
            }
        }
        $this->template->schools = $schools;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.schoolCheck.latte');
    }

    /**
     * @return ModelSchool[]
     */
    private function getSchoolsFromTeam(TeamModel2 $team): array
    {
        $schools = [];
        foreach ($team->getMembers() as $row) {
            $participant = TeamMemberModel::createFromActiveRow($row);
            $history = $participant->getPersonHistory();
            $schools[$history->school_id] = $history->school;
        }
        return $schools;
    }

    protected function createComponentValuePrinter(): ColumnPrinterComponent
    {
        return new ColumnPrinterComponent($this->getContext());
    }
}
