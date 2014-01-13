<?php

namespace PublicModule;

use AuthenticatedPresenter;
use Events\Machine\Machine;
use Events\Model\Holder;
use FKSDB\Components\Events\ApplicationComponent;
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
class ApplicationPresenter extends AuthenticatedPresenter {

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

    public function authorizedCreate($eventId) {
        $event = $this->getEvent();
        if (!$event) {
            throw new BadRequestException(_('Neexistující akce.'), 404);
        }
    }

    public function authorizedDefault($eventId, $id) {
        if (!$this->getEvent()) {
            throw new BadRequestException(_('Neexistující akce.'), 404);
        }
        if ($id && !$this->getEventApplication()) {
            throw new BadRequestException(_('Neexistující přihláška.'), 404);
        }
    }

    public function titleAuthorized($eventId, $id) {
        if ($this->getEventApplication()) {
            $this->setTitle("{$this->getEvent()} {$this->getEventApplication()}");
        } else {
            $this->setTitle("{$this->getEvent()}");
        }
    }

    public function actionDefault($eventId, $id) {
        $this->getHolder()->setModel($this->getEventApplication());
    }

    public function renderDefault($eventId, $id) {
        
    }

    protected function createComponentApplication($name) {
        $component = new ApplicationComponent($this->getMachine(), $this->getHolder());

        return $component;
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
