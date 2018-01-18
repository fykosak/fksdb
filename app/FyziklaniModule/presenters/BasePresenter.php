<?php

namespace FyziklaniModule;

use AuthenticatedPresenter;
use FKSDB\Components\Forms\Factories\FyziklaniFactory;
use ModelEvent;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use ORM\Services\Events\ServiceFyziklaniTeam;
use ServiceEvent;
use ServiceFyziklaniSubmit;
use ServiceFyziklaniTask;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
abstract class BasePresenter extends AuthenticatedPresenter {

    /**
     *
     * @var ModelEvent
     */
    private $event;

    /**
     * @var int $eventID
     * @persistent
     */
    public $eventID;

    /**
     * @var FyziklaniFactory
     */
    protected $fyziklaniFactory;

    /**
     *
     * @var Container
     */
    protected $container;

    /**
     *
     * @var ServiceEvent
     */
    protected $serviceEvent;

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


    public function injectServiceBrawlRoom(\ServiceBrawlRoom $serviceBrawlRoom) {
        $this->serviceBrawlRoom = $serviceBrawlRoom;
    }

    public function injectServiceBrawlTeamPosition(\ServiceBrawlTeamPosition $serviceBrawlTeamPosition) {
        $this->serviceBrawlTeamPosition = $serviceBrawlTeamPosition;
    }

    public function injectFyziklaniFactory(FyziklaniFactory $fyziklaniFactory) {
        $this->fyziklaniFactory = $fyziklaniFactory;
    }

    public function injectContainer(Container $container) {
        $this->container = $container;
    }

    public function injectServiceEvent(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
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

    public function startup() {

        $this->event = $this->getEvent();
        if (!$this->eventExist()) {
            throw new BadRequestException('Event nebyl nalezen.', 404);
        }
        parent::startup();
    }

    /** Vrati true ak pre daný ročník existuje fyzikláni */
    public function eventExist() {
        return $this->getEvent() ? true : false;
    }

    public function getSubtitle() {
        return (' ' . $this->getEvent()->event_year . '. FYKOSí Fyziklání');
    }

    /**
     * @return \ModelBrawlRoom[]
     */
    protected function getRooms() {
        return $this->serviceBrawlRoom->getRoomsByIds($this->getEvent()->getParameter('rooms'));
    }

    public function getEventId() {
        if (!$this->eventID) {
            $this->eventID = $this->serviceEvent->getTable()->where('event_type_id', 1)->max('event_id');
        }
        return $this->eventID;
    }

    /**
     * @deprecated
     * @return integer
     */
    public function getCurrentEventID() {
        return $this->getEventId();
    }

    /** vráti paramtre daného eventu
     * TODO rename to getEvent()
     * @return ModelEvent
     */
    public function getEvent() {
        if (!$this->event) {
            $this->event = $this->serviceEvent->findByPrimary($this->getEventId());
            if ($this->event) {
                $holder = $this->container->createEventHolder($this->getEvent());
                $this->event->setHolder($holder);
            }
        }
        return $this->event;
    }

    /** vráti paramtre daného eventu
     * @return ModelEvent
     * @deprecated
     */
    public function getCurrentEvent() {
        return $this->getEvent();
    }


    protected function eventIsAllowed($resource, $privilege) {
        $event = $this->getEvent();
        if (!$event) {
            return false;
        }
        return $this->getEventAuthorizator()->isAllowed($resource, $privilege, $event);
    }

}
