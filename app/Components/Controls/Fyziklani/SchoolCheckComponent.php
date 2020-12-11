<?php

namespace FKSDB\Components\Controls\Fyziklani;

use Fykosak\Utils\BaseComponent\BaseComponent;
use FKSDB\Components\Controls\DBReflection\ValuePrinter\ValuePrinterComponent;
use FKSDB\Model\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Model\ORM\Models\ModelEvent;
use FKSDB\Model\ORM\Models\ModelEventParticipant;
use FKSDB\Model\ORM\Models\ModelSchool;
use FKSDB\Model\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\Model\ORM\Services\ServiceSchool;
use Nette\DI\Container;

/**
 * Class SchoolCheckComponent
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SchoolCheckComponent extends BaseComponent {

    private ModelEvent $event;

    private int $acYear;

    private ServiceSchool $serviceSchool;

    private ServiceFyziklaniTeam $serviceFyziklaniTeam;

    public function __construct(ModelEvent $event, int $acYear, Container $container) {
        parent::__construct($container);
        $this->event = $event;
        $this->acYear = $acYear;
    }

    final public function injectPrimary(ServiceSchool $serviceSchool, ServiceFyziklaniTeam $serviceFyziklaniTeam): void {
        $this->serviceSchool = $serviceSchool;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    public function render(ModelFyziklaniTeam $currentTeam): void {
        $schools = [];
        $query = $this->serviceSchool->getContext()->query(
            'select GROUP_CONCAT(DISTINCT e_fyziklani_team_id) as `teams`, school_id
from event_participant ep
         JOIN person_history ph ON ph.person_id = ep.person_id and ac_year = ? and school_id IN (?)
         JOIN e_fyziklani_participant efp USING (event_participant_id)
         JOIN e_fyziklani_team eft USING (e_fyziklani_team_id)
WHERE ep.event_id = ?
group by school_id', ...[$this->acYear, array_keys($this->getSchoolsFromTeam($currentTeam)), $this->event->getPrimary()]);

        foreach ($query as $row) {
            $schools[$row->school_id] = array_map(function ($teamId): ?ModelFyziklaniTeam {
                return $this->serviceFyziklaniTeam->findByPrimary($teamId);
            }, explode(',', $row->teams));
            $schools[$row->school_id]['school'] = $this->serviceSchool->findByPrimary($row->school_id);
        }
        $this->template->schools = $schools;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.schoolCheck.latte');
        $this->template->render();
    }

    /**
     * @param ModelFyziklaniTeam $team
     * @return ModelSchool[]
     */
    private function getSchoolsFromTeam(ModelFyziklaniTeam $team): array {
        $schools = [];
        foreach ($team->getParticipants() as $row) {
            $participant = ModelEventParticipant::createFromActiveRow($row->event_participant);
            $history = $participant->getPerson()->getHistory($this->acYear);
            $schools[$history->school_id] = true;
        }
        return $schools;
    }

    protected function createComponentValuePrinter(): ValuePrinterComponent {
        return new ValuePrinterComponent($this->getContext());
    }
}
// there are very nice nette workaround, but very slow
/* foreach ($this->event->getTeams() as $row) {
      $team = ModelFyziklaniTeam::createFromActiveRow($row);
      $teamSchools = $this->getSchoolsFromTeam($team);

      foreach ($teamSchools as $schoolId => $school) {
          if (!array_key_exists($schoolId, $currentTeamSchools)) {
              continue;
          }
          if (!array_key_exists($schoolId, $schools)) {
              $schools[$schoolId] = [
                  'school' => $serviceSchool->findByPrimary($schoolId),
              ];
          }
          $schools[$schoolId][] = $team;
      }
 }*/
