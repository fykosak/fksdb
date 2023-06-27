<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls;

use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\Utils\BaseComponent\BaseComponent;
use FKSDB\Components\Controls\ColumnPrinter\ColumnRendererComponent;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\SchoolModel;
use Nette\DI\Container;

class SchoolCheckComponent extends BaseComponent
{

    private EventModel $event;

    public function __construct(EventModel $event, Container $container)
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
            $query = $this->event->getTeams()
                ->where(
                    ':fyziklani_team_member.person:person_history.ac_year',
                    $this->event->getContestYear()->ac_year
                )
                ->where(':fyziklani_team_member.person:person_history.school_id', $schoolId);
            foreach ($query as $team) {
                $schools[$schoolId][] = $team;
            }
        }
        $this->template->schools = $schools;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.schoolCheck.latte');
    }

    /**
     * @return SchoolModel[]
     */
    private function getSchoolsFromTeam(TeamModel2 $team): array
    {
        $schools = [];
        /** @var TeamMemberModel $member */
        foreach ($team->getMembers() as $member) {
            $history = $member->getPersonHistory();
            $schools[$history->school_id] = $history->school;
        }
        return $schools;
    }

    protected function createComponentValuePrinter(): ColumnRendererComponent
    {
        return new ColumnRendererComponent($this->getContext());
    }
}
