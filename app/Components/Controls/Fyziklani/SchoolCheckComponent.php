<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\DatabaseReflection\ValuePrinterComponent;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Models\ModelSchool;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\ORM\Services\ServiceSchool;
use Nette\DI\Container;

/**
 * Class SchoolCheckComponent
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SchoolCheckComponent extends BaseComponent {
    /**
     * @var ModelEvent
     */
    private $event;
    /**
     * @var int
     */
    private $acYear;

    /**
     * SchoolCheckControl constructor.
     * @param ModelEvent $event
     * @param int $acYear
     * @param Container $container
     */
    public function __construct(ModelEvent $event, int $acYear, Container $container) {
        parent::__construct($container);
        $this->event = $event;
        $this->acYear = $acYear;
    }

    /**
     * @param ModelFyziklaniTeam $currentTeam
     * @return void
     */
    public function render(ModelFyziklaniTeam $currentTeam) {
        $schools = [];

        /** @var ServiceSchool $serviceSchool */
        $serviceSchool = $this->getContext()->getByType(ServiceSchool::class);
        /** @var ServiceFyziklaniTeam $serviceFyziklaniTeam */
        $serviceFyziklaniTeam = $this->getContext()->getByType(ServiceFyziklaniTeam::class);
        $query = $serviceSchool->getConnection()->queryArgs(
            'select GROUP_CONCAT(DISTINCT e_fyziklani_team_id) as `teams`, school_id
from event_participant ep
         JOIN person_history ph ON ph.person_id = ep.person_id and ac_year = ? and school_id IN (?)
         JOIN e_fyziklani_participant efp USING (event_participant_id)
         JOIN e_fyziklani_team eft USING (e_fyziklani_team_id)
WHERE ep.event_id = ?
group by school_id', [$this->acYear, array_keys($this->getSchoolsFromTeam($currentTeam)), $this->event->getPrimary()]);
        foreach ($query as $row) {
            $schools[$row->school_id] = array_map(function ($teamId) use ($serviceFyziklaniTeam) {
                return $serviceFyziklaniTeam->findByPrimary($teamId);
            }, explode(',', $row->teams));
            $schools[$row->school_id]['school'] = $serviceSchool->findByPrimary($row->school_id);
        }
        $this->template->schools = $schools;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'SchoolCheckControl.latte');
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

    /**
     * @return ValuePrinterComponent
     * @throws \Exception
     */
    public function createComponentValuePrinter(): ValuePrinterComponent {
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
