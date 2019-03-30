<?php


namespace EventModule;


use FKSDB\Components\React\ReactComponent\Events\TeamApplicationsTimeProgress;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\Application\ForbiddenRequestException;

/**
 * Class ApplicationsTimeProgressPresenter
 * @package EventModule
 */
class ApplicationsTimeProgressPresenter extends BasePresenter {
    /**
     * @var \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;

    /**
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam $serviceFyziklaniTeam
     */
    public function injectServiceFyziklaniTeam(ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function authorizedDefault() {
        if (!\in_array($this->getEvent()->event_type_id, [1, 9])) {
            $this->setAuthorized(false);
            return;
        }
        $this->setAuthorized($this->eventIsAllowed('event.applicationsTimeProgress', 'default'));
    }

    public function titleDefault() {
        $this->setTitle(_('Applications time progress'));
        $this->setIcon('fa fa-line-chart');
    }

    /**
     * @return TeamApplicationsTimeProgress
     * @throws ForbiddenRequestException
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
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
