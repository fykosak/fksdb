<?php

namespace PublicModule;

use AuthenticatedPresenter;
use FKSDB\Components\Events\ApplicationComponent;
use ModelEvent;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
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
            throw new BadRequestException('Neexistující akce.', 404);
        }
    }

    public function titleCreate($eventId) {
        $this->setTitle($this->getEvent()->name);
    }

    public function renderCreate($eventId) {
        
    }

    protected function createComponentApplication($name) {
        $event = $this->getEvent();
        $machine = $this->container->createEventMachine($event);
        $holder = $this->container->createEventHolder($event);

        $component = new ApplicationComponent($machine, $holder);

        return $component;
    }

    private function getEvent() {
        if ($this->event === false) {
            $this->event = $this->serviceEvent->findByPrimary($this->getParam('eventId'));
        }

        return $this->event;
    }

}
