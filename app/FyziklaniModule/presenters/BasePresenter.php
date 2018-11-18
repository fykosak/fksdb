<?php

namespace FyziklaniModule;

use EventModule\BasePresenter as EventBasePresenter;
use FKSDB\Components\Controls\Choosers\BrawlChooser;
use FKSDB\Components\Forms\Factories\FyziklaniFactory;
use FKSDB\Components\React\Fyziklani\FyziklaniComponentsFactory;
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
     *
     * @var ServiceFyziklaniTeam
     */
    protected $serviceFyziklaniTeam;

    /**
     *
     * @var ServiceFyziklaniTask
     */
    protected $serviceFyziklaniTask;

    /**
     *
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
     * @return BrawlChooser
     */
    protected function createComponentBrawlChooser() {
        $control = new BrawlChooser($this->serviceEvent);

        return $control;
    }

    /**
     * @throws BadRequestException
     */
    public function startup() {
        parent::startup();
        if ($this->getEvent()->event_type_id !== 1) {
            $this->flashMessage('Event nieje fyziklani', 'warning');
            $this->redirect(':Event:Dashboard:default');
        }
        /**
         * @var $brawlChooser BrawlChooser
         */
        $brawlChooser = $this['brawlChooser'];
        $brawlChooser->setEvent($this->getEvent());
    }

    public function getSubtitle() {
        return sprintf(_('%d. Fyziklání'), $this->getEvent()->event_year);
    }

    /**
     * @return \ModelBrawlRoom[]
     */
    protected function getRooms() {
        return $this->serviceBrawlRoom->getRoomsByIds($this->getEvent()->getParameter('rooms'));
    }

    /*  public function getNavBarVariant() {
          return ['fyziklani fyziklani' . $this->getEventId(), 'dark'];
      }*/

    public function getNavRoot() {
        return 'fyziklani.dashboard.default';
    }

}
