<?php

namespace EventModule;

use FKSDB\Components\React\ReactComponent\Events\SingleApplicationsTimeProgress;
use FKSDB\Components\React\ReactComponent\Events\TeamApplicationsTimeProgress;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\ORM\Services\ServiceEventParticipant;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

/**
 * Class ApplicationsTimeProgressPresenter
 * @package EventModule
 */
class ApplicationsTimeProgressPresenter extends BasePresenter {
    private $eventIds = [
        1 => [1, 27, 95, 116, 125, 137, 145],
        2 => [2, 7, 92, 113, 123, 135, 143],
        3 => [3, 126, 35],
        7 => [6, 91, 124],
        11 => [111, 119, 129, 140],
        12 => [93, 115, 121, 136, 144],
        9 => [8, 94, 114, 122, 134, 141],
    ];
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;

    /**
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     */
    public function injectServiceFyziklaniTeam(ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    /**
     * @var ServiceEventParticipant
     */
    private $serviceEventParticipant;

    /**
     * @param ServiceEventParticipant $serviceEventParticipant
     */
    public function injectServiceEventParticipant(ServiceEventParticipant $serviceEventParticipant) {
        $this->serviceEventParticipant = $serviceEventParticipant;
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedDefault() {
        if (!array_key_exists($this->getEvent()->event_type_id, $this->eventIds)) {
            $this->setAuthorized(false);
            return;
        }
        $participant = $this->eventIsAllowed('event.participant', 'timeProgress');
        $team = $this->eventIsAllowed('fyziklani.team', 'timeProgress');
        $this->setAuthorized($team || $participant);
    }

    public function titleDefault() {
        $this->setTitle(_('Applications time progress'));
        $this->setIcon('fa fa-line-chart');
    }

    /**
     * @return TeamApplicationsTimeProgress
     * @throws ForbiddenRequestException
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function createComponentTeamApplicationsTimeProgress() {
        $events = [];
        foreach ($this->getEventIdsByType() as $id) {
            $row = $this->serviceEvent->findByPrimary($id);
            $events[$id] = ModelEvent::createFromActiveRow($row);
        }
        return new TeamApplicationsTimeProgress($this->context, $events, $this->serviceFyziklaniTeam);
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function renderDefault() {
        $this->template->teamEvents = self::TEAM_EVENTS;
        $this->template->eventType = $this->getEvent()->event_type_id;
    }

    /**
     * @return SingleApplicationsTimeProgress
     * @throws ForbiddenRequestException
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function createComponentSingleApplicationsTimeProgress() {
        $events = [];
        foreach ($this->getEventIdsByType() as $id) {
            $row = $this->serviceEvent->findByPrimary($id);
            $events[$id] = ModelEvent::createFromActiveRow($row);
        }
        return new SingleApplicationsTimeProgress($this->context, $events, $this->serviceEventParticipant);
    }

    /**
     * @return int[]
     * @throws ForbiddenRequestException
     * @throws AbortException
     * @throws BadRequestException
     * TODO hardcore eventIds
     */
    private function getEventIdsByType(): array {
        $typeId = $this->getEvent()->event_type_id;
        if (isset($this->eventIds[$typeId])) {
            return $this->eventIds[$typeId];
        }
        throw new ForbiddenRequestException();
    }

}
