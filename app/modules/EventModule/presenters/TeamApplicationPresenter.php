<?php

namespace EventModule;

use FKSDB\Components\Controls\Fyziklani\SeatingControl;
use FKSDB\Components\Grids\Events\Application\AbstractApplicationGrid;
use FKSDB\Components\Grids\Events\Application\ApplicationGrid;
use FKSDB\Components\Grids\Events\Application\TeamApplicationGrid;
use FKSDB\Components\React\ReactComponent\Events\TeamApplicationsTimeProgress;
use FKSDB\model\Fyziklani\NotSetGameParametersException;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

/**
 * Class ApplicationPresenter
 * @package EventModule
 * @method ModelFyziklaniTeam getEntity()
 */
class TeamApplicationPresenter extends AbstractApplicationPresenter {
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;
    /**
     * @var ServiceFyziklaniTeamPosition
     */
    private $serviceFyziklaniTeamPosition;

    /**
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     */
    public function injectServiceFyziklaniTeam(ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    /**
     * @param ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition
     */
    public function injectServiceFyziklaniTeamPosition(ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition) {
        $this->serviceFyziklaniTeamPosition = $serviceFyziklaniTeamPosition;
    }

    public function titleList() {
        $this->setTitle(_('List of team applications'));
        $this->setIcon('fa fa-users');
    }

    public function titleDetail() {
        $this->setTitle(_('Team application detail'));
        $this->setIcon('fa fa-user');
    }

    /**
     * @param ModelEvent $event
     * @return bool
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function isEnabledForEvent(ModelEvent $event): bool {
        return $this->isTeamEvent();
    }

    /**
     * @return ApplicationGrid
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentGrid(): AbstractApplicationGrid {
        return new TeamApplicationGrid($this->getEvent(), $this->getTableReflectionFactory());
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function renderDetail() {
        parent::renderDetail();
        $this->template->acYear = $this->getAcYear();
        try {
            $setup = $this->getEvent()->getFyziklaniGameSetup();
            $rankVisible = $setup->result_hard_display;
        } catch (NotSetGameParametersException $exception) {
            $rankVisible = false;
        }
        $this->template->rankVisible = $rankVisible;
        $this->template->model = $this->getEntity();
        $this->template->toPay = $this->getEntity()->getScheduleRest();
    }

    /**
     * @return SeatingControl
     */
    public function createComponentSeating(): SeatingControl {
        return new SeatingControl($this->serviceFyziklaniTeamPosition, $this->getTranslator());
    }

    /**
     * @return TeamApplicationsTimeProgress
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function createComponentTeamApplicationsTimeProgress() {
        $eventIds = [];
        switch ($this->getEvent()->event_type_id) {
            case 1:
                $eventIds = [30, 31, 32, /*33, 34,*/
                    1, 27, 95, 116, 125, 137, 145];
                break;
            case 9:
                $eventIds = [8, 94, 114, 122, 134, 141];
        }
        $events = [];
        foreach ($eventIds as $id) {
            $row = $this->serviceEvent->findByPrimary($id);
            $events[$id] = ModelEvent::createFromActiveRow($row);
        }
        return new TeamApplicationsTimeProgress($this->context, $events, $this->serviceFyziklaniTeam);
    }

    /**
     * @return AbstractServiceSingle
     */
    function getORMService() {
        return $this->serviceFyziklaniTeam;
    }

    /**
     * @return string
     */
    protected function getModelResource(): string {
        return ModelFyziklaniTeam::RESOURCE_ID;
    }
}
