<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Fyziklani;

use Fykosak\Utils\BaseComponent\BaseComponent;
use FKSDB\Components\Controls\ColumnPrinter\ColumnPrinterComponent;
use FKSDB\Models\ORM\Models\Events\ModelFyziklaniParticipant;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
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

    final public function render(ModelFyziklaniTeam $currentTeam): void
    {
        $schools = [];
        foreach ($this->getSchoolsFromTeam($currentTeam) as $schoolId => $school) {
            $schools[$schoolId] = [
                'school' => $school,
            ];
            $query = $this->event->getTeams()
                ->where(
                    ':e_fyziklani_participant.event_participant.person:person_history.ac_year',
                    $this->event->getContestYear()->ac_year
                )
                ->where(':e_fyziklani_participant.event_participant.person:person_history.school_id', $schoolId);
            foreach ($query as $team) {
                $schools[$schoolId][] = ModelFyziklaniTeam::createFromActiveRow($team);
            }
        }
        $this->template->schools = $schools;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.schoolCheck.latte');
    }

    /**
     * @return ModelSchool[]
     */
    private function getSchoolsFromTeam(ModelFyziklaniTeam $team): array
    {
        $schools = [];
        foreach ($team->getFyziklaniParticipants() as $row) {
            $participant = ModelFyziklaniParticipant::createFromActiveRow($row)->getEventParticipant();
            $history = $participant->getPersonHistory();
            $schools[$history->school_id] = $history->getSchool();
        }
        return $schools;
    }

    protected function createComponentValuePrinter(): ColumnPrinterComponent
    {
        return new ColumnPrinterComponent($this->getContext());
    }
}
