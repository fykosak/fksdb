<?php

namespace PublicModule;

use Events\Machine\Machine;
use Events\Model\Grid\RelatedPersonSource;
use Events\Model\Holder;
use FKSDB\Components\Controls\ContestChooser;
use FKSDB\Components\Events\ApplicationComponent;
use FKSDB\Components\Events\ApplicationsGrid;
use ModelEvent;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use ORM\IModel;
use ServiceEvent;
use SystemContainer;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ApplicationPresenter extends BasePresenter {

    /**
     * @var ServiceEvent
     */
    private $serviceEvent;

    /**
     * @var ModelEvent
     */
    private $event = false;

    /**
     * @var IModel
     */
    private $eventApplication = false;

    /**
     * @var Holder 
     */
    private $holder;

    /**
     * @var Machine
     */
    private $machine;

    /**
     *
     * @var SystemContainer
     */
    private $container;

    public function injectServiceEvent(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    public function injectContainer(Container $container) {
        $this->container = $container;
    }

    public function authorizedDefault($eventId, $id) {
        if (!$this->getEvent()) {
            throw new BadRequestException(_('Neexistující akce.'), 404);
        }
        if ($id && !$this->getEventApplication()) {
            throw new BadRequestException(_('Neexistující přihláška.'), 404);
        }
    }

    public function authorizedList() {
        return $this->getUser()->isLoggedIn() && $this->getUser()->getIdentity()->getPerson();
    }

    public function titleAuthorized($eventId, $id) {
        if ($this->getEventApplication()) {
            $this->setTitle("{$this->getEvent()} {$this->getEventApplication()}");
        } else {
            $this->setTitle("{$this->getEvent()}");
        }
    }

    public function titleList() {
        $this->setTitle(sprintf(_('Moje přihlášky (%s)'), $this->getSelectedContest()->name));
    }

    public function actionDefault($eventId, $id) {
        $this->getHolder()->setModel($this->getEventApplication());
    }

    public function actionList() {
        
    }

    protected function createComponentContestChooser($name) {
        $component = parent::createComponentContestChooser($name);
        if ($this->getAction() == 'default') {
            $component->setContests(array(
                $this->getEvent()->getEventType()->contest_id,
            ));
        } else if ($this->getAction() == 'list') {
            $component->setContests(ContestChooser::ALL_CONTESTS);
        }
        return $component;
    }

    protected function createComponentApplication($name) {
        $component = new ApplicationComponent($this->getMachine(), $this->getHolder());

        return $component;
    }

    protected function createComponentApplicationsGrid($name) {
        $person = $this->getUser()->getIdentity()->getPerson();
        $events = $this->serviceEvent->getTable();
        $events->where('event_type.contest_id', $this->getSelectedContest()->contest_id);

        $source = new RelatedPersonSource($person, $events, $this->container);
        $grid = new ApplicationsGrid($this->container, $source);

        return $grid;
    }

    private function getEvent() {
        if ($this->event === false) {
            $this->event = $this->serviceEvent->findByPrimary($this->getParameter('eventId'));
        }

        return $this->event;
    }

    private function getEventApplication() {
        if ($this->eventApplication === false) {
            $service = $this->getHolder()->getPrimaryHolder()->getService();
            $this->eventApplication = $service->findByPrimary($this->getParameter('id'));
        }

        return $this->eventApplication;
    }

    private function getHolder() {
        if (!$this->holder) {
            $this->holder = $this->container->createEventHolder($this->getEvent());
        }
        return $this->holder;
    }

    private function getMachine() {
        if (!$this->machine) {
            $this->machine = $this->container->createEventMachine($this->getEvent());
        }
        return $this->machine;
    }

}
