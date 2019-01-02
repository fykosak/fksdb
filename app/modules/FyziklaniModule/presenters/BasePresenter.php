<?php

namespace FyziklaniModule;

use EventModule\BasePresenter as EventBasePresenter;
use FKSDB\Components\Controls\Choosers\FyziklaniChooser;
use FKSDB\Components\Forms\Factories\FyziklaniFactory;
use FKSDB\Components\React\Fyziklani\FyziklaniComponentsFactory;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniGameSetup;
use Nette\Application\BadRequestException;
use ORM\Services\Events\ServiceFyziklaniTeam;
use ServiceFyziklaniSubmit;
use ServiceFyziklaniTask;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
abstract class BasePresenter extends EventBasePresenter {

    /**
     * @var FyziklaniFactory
     */
    protected $fyziklaniFactory;

    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;

    /**
     * @var ServiceFyziklaniTask
     */
    private $serviceFyziklaniTask;

    /**
     * @var ServiceFyziklaniSubmit
     */
    private $serviceFyziklaniSubmit;

    /**
     * @var \ServiceBrawlRoom
     */
    private $serviceBrawlRoom;

    /**
     * @var \ServiceBrawlTeamPosition
     */
    private $serviceBrawlTeamPosition;

    /**
     * @var FyziklaniComponentsFactory
     */
    protected $fyziklaniComponentsFactory;
    /**
     * @var ModelFyziklaniGameSetup
     */
    private $gameSetup;


    public function injectServiceBrawlRoom(\ServiceBrawlRoom $serviceBrawlRoom) {
        $this->serviceBrawlRoom = $serviceBrawlRoom;
    }

    protected function getServiceFyziklaniRoom(): \ServiceBrawlRoom {
        return $this->serviceBrawlRoom;
    }

    public function injectFyziklaniComponentsFactory(FyziklaniComponentsFactory $fyziklaniComponentsFactory) {
        $this->fyziklaniComponentsFactory = $fyziklaniComponentsFactory;
    }

    public function injectServiceBrawlTeamPosition(\ServiceBrawlTeamPosition $serviceBrawlTeamPosition) {
        $this->serviceBrawlTeamPosition = $serviceBrawlTeamPosition;
    }

    protected function getServiceFyziklaniTeamPosition(): \ServiceBrawlTeamPosition {
        return $this->serviceBrawlTeamPosition;
    }

    public function injectFyziklaniFactory(FyziklaniFactory $fyziklaniFactory) {
        $this->fyziklaniFactory = $fyziklaniFactory;
    }

    public function injectServiceFyziklaniTeam(ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    /**
     * @return ServiceFyziklaniTeam
     */
    protected function getServiceFyziklaniTeam(): ServiceFyziklaniTeam {
        return $this->serviceFyziklaniTeam;
    }

    public function injectServiceFyziklaniTask(ServiceFyziklaniTask $serviceFyziklaniTask) {
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
    }

    /**
     * @return ServiceFyziklaniTask
     */
    protected function getServiceFyziklaniTask(): ServiceFyziklaniTask {
        return $this->serviceFyziklaniTask;
    }

    public function injectServiceFyziklaniSubmit(ServiceFyziklaniSubmit $serviceFyziklaniSubmit) {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
    }

    /**
     * @return ServiceFyziklaniSubmit
     */
    protected function getServiceFyziklaniSubmit(): ServiceFyziklaniSubmit {
        return $this->serviceFyziklaniSubmit;
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
            $this->flashMessage('Event nieje fyziklani', 'warning');
            $this->redirect(':Event:Dashboard:default');
        }
        /**
         * @var $fyziklaniChooser FyziklaniChooser
         */
        $fyziklaniChooser = $this['fyziklaniChooser'];
        $fyziklaniChooser->setEvent($this->getEvent());
    }

    /**
     * @return array
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    protected function getRooms() {
        return $this->getServiceFyziklaniRoom()->getRoomsByIds($this->getEvent()->getParameter('gameSetup')['rooms']);
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
                throw new BadRequestException(_('Herné paramtre niesu nastavené'));
            }
            $this->gameSetup = $gameSetup;
        }
        return $this->gameSetup;
    }

}
