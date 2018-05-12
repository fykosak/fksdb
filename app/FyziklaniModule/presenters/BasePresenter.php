<?php

namespace FyziklaniModule;

use AuthenticatedPresenter;
use FKSDB\Components\Forms\Factories\FyziklaniFactory;
use ModelEvent;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Diagnostics\Debugger;
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
     * @var  int $year
     * @persistent
     */
    public $year;

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

    /**
     * @throws BadRequestException
     */
    public function startup() {

        $this->event = $this->getEvent();
        if (!$this->eventExist()) {
            throw new BadRequestException('Event nebyl nalezen.', 404);
        }
        parent::startup();
    }

    /**
     * @return bool
     */
    protected function eventExist() {
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

    /**
     * @return integer
     */
    public function getEventId() {
        return $this->getEvent()->event_id;
    }

    /**
     * @return integer
     */
    public function getYear() {
         if (!$this->year) {
            $this->year = $this->serviceEvent->getTable()->where('event_type_id', 1)->max('YEAR(begin)');
        }
        return $this->year;
    }

    /**
     * @return ModelEvent
     */
    public function getEvent() {
        if (!$this->event) {
            $this->event = $this->serviceEvent->getTable()->where('event_type_id', 1)->where('YEAR(begin)=?', $this->getYear())->fetch();
            if ($this->event) {
                $holder = $this->container->createEventHolder($this->getEvent());
                $this->event->setHolder($holder);
            }
        }
        return $this->event;
    }

    protected function eventIsAllowed($resource, $privilege) {
        $event = $this->getEvent();
        if (!$event) {
            return false;
        }
        return $this->getEventAuthorizator()->isAllowed($resource, $privilege, $event);
    }

    public function getNavBarVariant() {
        return ['brawl brawl' . $this->getEventId(), 'dark'];
    }

}
