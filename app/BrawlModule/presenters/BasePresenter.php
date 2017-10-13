<?php

namespace BrawlModule;

use AuthenticatedPresenter;
use FKSDB\Components\Controls\BrawlNav\BrawlNav;
use FKSDB\Components\Forms\Factories\BrawlFactory;
use ModelEvent;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Diagnostics\Debugger;
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
     * @var string $lang
     * @persistent
     */
    public $lang;

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
        /**
         * @var $brawlNav BrawlNav
         */
        $brawlNav = $this['brawlNav'];

        $newParams = $brawlNav->init((object)['eventId' => +$this->eventId, 'lang' => $this->lang]);
        if ($newParams) {
            $this->redirect('this', [
                'eventId' => $newParams->eventId ?: $this->getEventId(),
                'lang' => $newParams->lang ?: $this->lang,
            ]);
        }
        if (!$this->getEvent()) {
            throw new BadRequestException('Event nebyl nalezen.', 404);
        }
        parent::startup();
    }

    public function getLang() {
        return $this->lang ?: parent::getLang();
    }

    public function createComponentBrawlNav() {
        $control = new BrawlNav($this->serviceEvent, $this->session);
        return $control;
    }

    public function getSubtitle() {
        return ' ' . $this->getEvent()->event_year . '. ' . _('FYKOSí Fyziklání');
    }

    public function getEventId() {
        return $this->eventId;
    }

    /** vráti paramtre daného eventu
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

    protected function eventIsAllowed($resource, $privilege) {
        $event = $this->getEvent();
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
