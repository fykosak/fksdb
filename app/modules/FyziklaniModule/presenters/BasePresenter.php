<?php

namespace FyziklaniModule;

use EventModule\BasePresenter as EventBasePresenter;
use FKSDB\Components\Controls\Choosers\FyziklaniChooser;
use FKSDB\Components\Factories\FyziklaniFactory;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniGameSetup;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use Nette\Application\BadRequestException;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;

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
     * @var \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask
     */
    private $serviceFyziklaniTask;

    /**
     * @var \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition
     */
    private $serviceFyziklaniTeamPosition;

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
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition
     */
    public function injectServiceFyziklaniTeamPosition(\FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition) {
        $this->serviceFyziklaniTeamPosition = $serviceFyziklaniTeamPosition;
    }

    /**
     * @return \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition
     */
    protected function getServiceFyziklaniTeamPosition(): \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition {
        return $this->serviceFyziklaniTeamPosition;
    }

    /**
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam $serviceFyziklaniTeam
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
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask $serviceFyziklaniTask
     */
    public function injectServiceFyziklaniTask(ServiceFyziklaniTask $serviceFyziklaniTask) {
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
    }

    /**
     * @return \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask
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
