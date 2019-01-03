<?php


namespace EventModule;


use FKSDB\Components\React\ReactComponent\Events\TeamApplicationsTimeProgress;
use FKSDB\ORM\ModelEvent;
use Nette\Application\ForbiddenRequestException;
use ORM\Services\Events\ServiceFyziklaniTeam;

class ApplicationsTimeProgressPresenter extends BasePresenter {
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;

    public function injectServiceFyziklaniTeam(ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    public function authorizedDefault() {
       $this->setAuthorized($this->eventIsAllowed('event.applicationsTimeProgress', 'default'));
    }

    public function titleDefault() {
        $this->setTitle(_('Applications time progress'));
        $this->setIcon('fa fa-line-chart');
    }

    protected function createComponentTeamApplicationsTimeProgress() {
        $events = [];
        foreach ($this->getEventIdsByType() as $id) {
            $row = $this->serviceEvent->findByPrimary($id);
            $events[$id] = ModelEvent::createFromTableRow($row);
        }

        return new TeamApplicationsTimeProgress($this->context, $events, $this->serviceFyziklaniTeam);
    }

    /**
     * @return int[]
     * @throws ForbiddenRequestException
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     * @TODO hardcore eventIds
     */
    private function getEventIdsByType(): array {
        switch ($this->getEvent()->event_type_id) {
            case 1:
                return [1, 27, 95, 116, 125, 137];
            case 9:
                return [8, 94, 114, 122, 134];
            default:
                throw new ForbiddenRequestException();

        }
    }

}
