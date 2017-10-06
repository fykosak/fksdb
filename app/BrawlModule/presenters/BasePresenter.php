<?php

namespace BrawlModule;

use AuthenticatedPresenter;
use FKSDB\Components\Controls\BrawlNav\BrawlNav;
use FKSDB\Components\Forms\Factories\BrawlFactory;
use ModelEvent;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use ORM\Services\Events\ServiceFyziklaniTeam as ServiceBrawlTeam;
use ServiceEvent;
use ServiceBrawlSubmit;
use ServiceBrawlTask;

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
     * @var integer $eventID
     * @persistent
     */
    public $eventId;

    /**
     * @var BrawlFactory
     */
    protected $brawlFactory;

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
     * @var ServiceBrawlTeam
     */
    protected $serviceBrawlTeam;

    /**
     *
     * @var ServiceBrawlTask
     */
    protected $serviceBrawlTask;

    /**
     *
     * @var ServiceBrawlSubmit
     */
    protected $serviceBrawlSubmit;

    public function injectBrawlFactory(BrawlFactory $brawlFactory) {
        $this->brawlFactory = $brawlFactory;
    }

    public function injectContainer(Container $container) {
        $this->container = $container;
    }

    public function injectServiceEvent(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    public function injectServiceBrawlTeam(ServiceBrawlTeam $serviceBrawlTeam) {
        $this->serviceBrawlTeam = $serviceBrawlTeam;
    }

    public function injectServiceBrawlTask(ServiceBrawlTask $serviceBrawlTask) {
        $this->serviceBrawlTask = $serviceBrawlTask;
    }

    public function injectServiceBrawlSubmit(ServiceBrawlSubmit $serviceBrawlSubmit) {
        $this->serviceBrawlSubmit = $serviceBrawlSubmit;
    }

    public function startup() {
        $this['brawlNav']->syncRedirect((object)['eventId' => +$this->eventId]);
        $this->event = $this->getCurrentEvent();
        if (!$this->eventExist()) {
            throw new BadRequestException('Event nebyl nalezen.', 404);
        }

        parent::startup();
    }

    public function createComponentBrawlNav($name) {
        $control = new BrawlNav($this->serviceEvent);
        return $control;
    }

    /** Vrati true ak pre daný ročník existuje fyzikláni */
    public function eventExist() {
        return $this->getCurrentEvent() ? true : false;
    }

    public function getSubtitle() {
        return (' ' . $this->getCurrentEvent()->event_year . '. FYKOSí Fyziklání');
    }

    public function getEventId() {
        return $this->eventId;
    }

    /** vráti paramtre daného eventu
     * TODO rename to getEvent()
     * @return ModelEvent
     */
    public function getCurrentEvent() {
        if (!$this->event) {
            $this->event = $this->serviceEvent->findByPrimary($this->getEventId());
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

    public function getSelectedContestSymbol() {
        return 'brawl';
    }

    public function getNavRoot() {
        return 'brawl.dashboard.default';
    }
}
