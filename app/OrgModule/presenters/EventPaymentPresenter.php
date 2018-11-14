<?php


namespace OrgModule;


use FKSDB\Components\Forms\Factories\EventPaymentFactory;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Grids\EventPayment\OrgEventPaymentGrid;
use FKSDB\EventPayment\Transition\Machine;
use FKSDB\EventPayment\Transition\TransitionsFactory;
use FKSDB\ORM\ModelEvent;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\NotImplementedException;

class EventPaymentPresenter extends EntityPresenter {
    /**
     * @var \ServiceEventPayment
     */
    private $serviceEventPayment;

    /**
     * @var PersonFactory
     */
    private $personFactory;

    /**
     * @var integer
     * @persistent
     */
    public $eventId;
    /**
     * @var TransitionsFactory
     */
    private $transitionsFactory;
    /**
     * @var EventPaymentFactory
     */
    private $eventPaymentFactory;

    /**
     * @var Machine
     */
    private $machine;

    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * @var \ServiceEvent
     */
    private $serviceEvent;

    /**
     * @var Container
     */
    private $container;

    public function injectServiceEventPayment(\ServiceEventPayment $serviceEventPayment) {
        $this->serviceEventPayment = $serviceEventPayment;
    }

    public function injectServicePersonFactory(PersonFactory $personFactory) {
        $this->personFactory = $personFactory;
    }

    public function injectEventTransitionFactory(TransitionsFactory $transitionsFactory) {
        $this->transitionsFactory = $transitionsFactory;
    }

    public function injectEventPaymentFactory(EventPaymentFactory $eventPaymentFactory) {
        $this->eventPaymentFactory = $eventPaymentFactory;
    }

    public function injectServiceEvent(\ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    public function injectContainer(Container $container) {
        $this->container = $container;
    }

    protected function createComponentCreateComponent($name) {
        throw new NotImplementedException('use public GUI');
    }

    private function getMachine(): Machine {
        if (!$this->machine) {
            $this->machine = $this->transitionsFactory->setUpMachine($this->getEvent());
        }
        return $this->machine;
    }

    private function getEvent(): ModelEvent {
        if (!$this->event) {
            if (!$this->eventId) {
                throw new BadRequestException('EventId je povinné');
            }
            $row = $this->serviceEvent->findByPrimary($this->eventId);
            if (!$row) {
                throw new BadRequestException('Event nenájdený');
            }
            $this->event = ModelEvent::createFromTableRow($row);
            $holder = $this->container->createEventHolder($this->event);
            $this->event->setHolder($holder);
        }
        return $this->event;
    }


    protected function createComponentGrid($name) {
        return new OrgEventPaymentGrid($this->getMachine(),$this->serviceEventPayment, $this->transitionsFactory, $this->eventId);
    }

    protected function createComponentEditComponent($name) {
        $control = $this->eventPaymentFactory->createEditForm(true);

        return $control;
    }

    protected function loadModel($id) {
        return $this->serviceEventPayment->findByPrimary($id);
    }
}
