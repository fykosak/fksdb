<?php

namespace FyziklaniModule;

use EventModule\BasePresenter as EventBasePresenter;
use FKSDB\Components\Controls\Choosers\FyziklaniChooser;
use FKSDB\Components\Factories\FyziklaniFactory;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniGameSetup;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition;
use Nette\Application\BadRequestException;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
abstract class BasePresenter extends EventBasePresenter {

    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;

    /**
     * @var ServiceFyziklaniTask
     */
    private $serviceFyziklaniTask;

    /**
     * @var ServiceFyziklaniTeamPosition
     */
    private $serviceFyziklaniTeamPosition;
    /**
     * @var ServiceFyziklaniSubmit
     */
    private $serviceFyziklaniSubmit;

    /**
     * @var FyziklaniFactory
     */
    protected $fyziklaniComponentsFactory;
    /**
     * @var ModelFyziklaniGameSetup
     */
    private $gameSetup;

    /**
     * @param FyziklaniFactory $fyziklaniComponentsFactory
     */
    public function injectFyziklaniComponentsFactory(FyziklaniFactory $fyziklaniComponentsFactory) {
        $this->fyziklaniComponentsFactory = $fyziklaniComponentsFactory;
    }

    /**
     * @param ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     */
    public function injectServiceFyziklaniSubmit(ServiceFyziklaniSubmit $serviceFyziklaniSubmit) {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
    }

    /**
     * @param ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition
     */
    public function injectServiceFyziklaniTeamPosition(ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition) {
        $this->serviceFyziklaniTeamPosition = $serviceFyziklaniTeamPosition;
    }

    /**
     * @return ServiceFyziklaniTeamPosition
     */
    protected function getServiceFyziklaniTeamPosition(): ServiceFyziklaniTeamPosition {
        return $this->serviceFyziklaniTeamPosition;
    }

    /**
     * @return ServiceFyziklaniSubmit
     */
    protected function getServiceFyziklaniSubmit(): ServiceFyziklaniSubmit {
        return $this->serviceFyziklaniSubmit;
    }

    /**
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     */
    public function injectServiceFyziklaniTeam(ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    /**
     * @return ServiceFyziklaniTeam
     */
    protected function getServiceFyziklaniTeam(): ServiceFyziklaniTeam {
        return $this->serviceFyziklaniTeam;
    }

    /**
     * @param ServiceFyziklaniTask $serviceFyziklaniTask
     */
    public function injectServiceFyziklaniTask(ServiceFyziklaniTask $serviceFyziklaniTask) {
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
    }

    /**
     * @return ServiceFyziklaniTask
     */
    protected function getServiceFyziklaniTask(): ServiceFyziklaniTask {
        return $this->serviceFyziklaniTask;
    }

    /**
     * @return FyziklaniChooser
     */
    protected function createComponentFyziklaniChooser(): FyziklaniChooser {
        return new FyziklaniChooser($this->serviceEvent);
    }

    /**
     * @return bool
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    protected function isEventFyziklani(): bool {
        return $this->getEvent()->event_type_id === 1;
    }

    /**
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    protected function startup() {
        parent::startup();
        if (!$this->isEventFyziklani()) {
            $this->flashMessage('Event nieje fyziklani', \BasePresenter::FLASH_WARNING);
            $this->redirect(':Event:Dashboard:default');
        }
        /**
         * @var FyziklaniChooser $fyziklaniChooser
         */
        $fyziklaniChooser = $this->getComponent('fyziklaniChooser');
        $fyziklaniChooser->setEvent($this->getEvent());
    }

    /**
     * @return string[]
     */
    protected function getNavRoots(): array {
        return ['fyziklani.dashboard.default'];
    }

    /**
     * @return int
     */
    protected function getEventId(): int {
        if (!$this->eventId) {
            $this->eventId = $this->serviceEvent->getTable()->where('event_type_id', 1)->max('event_id');
        }
        return $this->eventId;
    }

    /**
     * @return ModelFyziklaniGameSetup
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    protected function getGameSetup(): ModelFyziklaniGameSetup {
        if (!$this->gameSetup) {
            $gameSetup = $this->getEvent()->getFyziklaniGameSetup();
            if (!$gameSetup) {
                throw new BadRequestException(_('Game is not set up!'), 404);
            }
            $this->gameSetup = $gameSetup;
        }
        return $this->gameSetup;
    }

}
