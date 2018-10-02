<?php

namespace FyziklaniModule;

use AuthenticatedPresenter;
use FKSDB\Components\Controls\Choosers\BrawlChooser;
use FKSDB\Components\Controls\LanguageChooser;
use FKSDB\Components\Forms\Factories\FyziklaniFactory;
use FKSDB\ORM\ModelEvent;
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
     * @var \FKSDB\ORM\ModelEvent
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

    /**
     * @return BrawlChooser
     */
    protected function createComponentBrawlChooser() {
        $control = new BrawlChooser($this->serviceEvent);

        return $control;
    }

    protected function createComponentLanguageChooser() {
        $control = new LanguageChooser($this->session);

        return $control;
    }

    /**
     * @throws BadRequestException
     */
    public function startup() {
        /**
         * @var $languageChooser LanguageChooser
         * @var $brawlChooser BrawlChooser
         */
        $languageChooser = $this['languageChooser'];
        $brawlChooser = $this['brawlChooser'];
        $languageChooser->syncRedirect();
        $brawlChooser->setEvent($this->getEvent());

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
        return sprintf(_('%d. Fyziklání'),$this->getEvent()->event_year);
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
        if (!$this->eventID) {
            $this->eventID = $this->serviceEvent->getTable()->where('event_type_id', 1)->max('event_id');
        }
        return $this->eventID;
    }

    /**
     * @return \FKSDB\ORM\ModelEvent
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

    public function getNavBarVariant() {
        return ['brawl brawl' . $this->getEventId(), 'dark'];
    }
    public function getNavRoot() {
        return 'fyziklani.dashboard.default';
    }

}
