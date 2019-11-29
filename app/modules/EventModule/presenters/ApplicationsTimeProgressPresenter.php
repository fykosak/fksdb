<?php

namespace EventModule;

use FKSDB\Components\React\ReactComponent\Events\TeamApplicationsTimeProgress;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use function in_array;
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
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedDefault() {
        if (!in_array($this->getEvent()->event_type_id, [1, 9])) {
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
