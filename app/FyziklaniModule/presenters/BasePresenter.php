<?php

namespace FyziklaniModule;

use EventModule\BasePresenter as EventBasePresenter;
use FKSDB\Components\Controls\Choosers\FyziklaniChooser;
use FKSDB\Components\Forms\Factories\FyziklaniFactory;
use FKSDB\Components\React\Fyziklani\FyziklaniComponentsFactory;
use FKSDB\model\Fyziklani\NotSetGameParametersException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniGameSetup;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniGameSetup;
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
    protected $serviceFyziklaniTeam;

    /**
     * @var ServiceFyziklaniTask
     */
    protected $serviceFyziklaniTask;

    /**
     * @var ServiceFyziklaniSubmit
     */
    protected $serviceFyziklaniSubmit;

    /**
     * @var \ServiceBrawlRoom
     */
    protected $serviceBrawlRoom;

    /**
     * @var \ServiceBrawlTeamPosition
     */
    protected $serviceBrawlTeamPosition;

    /**
     * @var FyziklaniComponentsFactory
     */
    protected $fyziklaniComponentsFactory;
    /**
     * @var
     */
    protected $serviceFyziklaniGameSetup;
    /**
     * @var ModelFyziklaniGameSetup
     */
    private $gameSetup;

    public function injectServiceFyziklaniGameSetup(ServiceFyziklaniGameSetup $serviceFyziklaniGameSetup) {
        $this->serviceFyziklaniGameSetup = $serviceFyziklaniGameSetup;
    }


    public function injectServiceBrawlRoom(\ServiceBrawlRoom $serviceBrawlRoom) {
        $this->serviceBrawlRoom = $serviceBrawlRoom;
    }

    public function injectFyziklaniComponentsFactory(FyziklaniComponentsFactory $fyziklaniComponentsFactory) {
        $this->fyziklaniComponentsFactory = $fyziklaniComponentsFactory;
    }

    public function injectServiceBrawlTeamPosition(\ServiceBrawlTeamPosition $serviceBrawlTeamPosition) {
        $this->serviceBrawlTeamPosition = $serviceBrawlTeamPosition;
    }

    public function injectFyziklaniFactory(FyziklaniFactory $fyziklaniFactory) {
        $this->fyziklaniFactory = $fyziklaniFactory;
    }

    public function injectServiceFyziklaniTeam(ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    public function injectServiceFyziklaniTask(ServiceFyziklaniTask $serviceFyziklaniTask) {
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
    }

    public function injectServiceFyziklaniSubmit(ServiceFyziklaniSubmit $serviceFyziklaniSubmit) {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
    }

    /**
     * @return FyziklaniChooser
     */
    protected function createComponentBrawlChooser() {
        $control = new FyziklaniChooser($this->serviceEvent);

        return $control;
    }

    /**
     * @return bool
     * @throws BadRequestException
     */
    protected function isEventFyziklani(): bool {
        return $this->getEvent()->event_type_id === 1;
    }

    /**
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function startup() {
        parent::startup();
        if (!$this->isEventFyziklani()) {
            $this->flashMessage('Event nieje fyziklani', 'warning');
            $this->redirect(':Event:Dashboard:default');
        }
        /**
         * @var $brawlChooser FyziklaniChooser
         */
        $brawlChooser = $this['brawlChooser'];
        $brawlChooser->setEvent($this->getEvent());
    }

    public function getSubtitle() {
        return $this->getEvent()->name;
        // return sprintf(_('fyziklani%d'), $this->getEvent()->begin->format('Y'));
    }

    /**
     * @return array
     * @throws BadRequestException
     */
    protected function getRooms() {
        return $this->serviceBrawlRoom->getRoomsByIds($this->getEvent()->getParameter('gameSetup')['rooms']);
    }

    /*  public function getNavBarVariant() {
          return ['fyziklani fyziklani' . $this->getEventId(), 'dark'];
      }*/

    public function getNavRoot() {
        return 'fyziklani.dashboard.default';
    }

    /**
     * @return int
     */
    public function getEventId() {
        if (!$this->eventId) {
            $this->eventId = $this->serviceEvent->getTable()->where('event_type_id', 1)->max('event_id');
        }
        return $this->eventId;
    }

    /**
     * @return ModelFyziklaniGameSetup
     * @throws BadRequestException
     */
    protected function getGameSetup(): ModelFyziklaniGameSetup {
        if (!$this->gameSetup) {
            $this->gameSetup = $this->getEvent()->getFyziklaniGameSetup();
        }
        return $this->gameSetup;
    }

}
