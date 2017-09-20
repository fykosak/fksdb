<?php

namespace FyziklaniModule;

use AuthenticatedPresenter;
use Events\Model\Holder\Holder;
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

        $this->event = $this->getCurrentEvent();
        if (!$this->eventExist()) {
            throw new BadRequestException('Event nebyl nalezen.', 404);
        }
        parent::startup();
    }

    /** Vrati true ak pre daný ročník existuje fyzikláni */
    public function eventExist() {
        return $this->getCurrentEvent() ? true : false;
    }

    public function getSubtitle() {
        return (' ' . $this->getCurrentEvent()->event_year . '. FYKOSí Fyziklání');
    }

    public function getCurrentEventID() {
        if (!$this->eventID) {
            $this->eventID = $this->serviceEvent->getTable()->where('event_type_id', 1)->max('event_id');
        }
        return $this->eventID;
    }

    /** vráti paramtre daného eventu
     * TODO rename to getEvent()
     * @return ModelEvent
     */
    public function getCurrentEvent() {
        if (!$this->event) {
            $this->event = $this->serviceEvent->findByPrimary($this->getCurrentEventID());
            if ($this->event) {
                $holder = $this->container->createEventHolder($this->getCurrentEvent());
                $this->event->setHolder($holder);
            }
        }
        return $this->event;
    }

    protected function eventIsAllowed($resource, $privilege) {
        $event = $this->getCurrentEvent();
        if (!$event) {
            return false;
        }
        return $this->getEventAuthorizator()->isAllowed($resource, $privilege, $event);
    }

    public function getSelectedContest() {
        return 'brawl';
    }
}
