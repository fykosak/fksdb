<?php

namespace PublicModule;

use FKSDB\Components\Forms\Controls\EventPayment\DetailControl;
use FKSDB\Components\Forms\Factories\EventPaymentFactory;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\EventPayment\PriceCalculator\PriceCalculator;
use FKSDB\EventPayment\PriceCalculator\PriceCalculatorFactory;
use FKSDB\EventPayment\SymbolGenerator\AbstractSymbolGenerator;
use FKSDB\EventPayment\SymbolGenerator\SymbolGeneratorFactory;
use FKSDB\EventPayment\Transition\Machine;
use FKSDB\EventPayment\Transition\TransitionsFactory;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelEventPayment;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\DI\Container;

class EventPaymentPresenter extends BasePresenter {
    /**
     * @var \ServiceEventPayment
     */
    private $serviceEventPayment;

    /**
     * @var ReferencedPersonFactory
     */
    private $referencedPersonFactory;

    /**
     * @var integer
     * @persistent
     */
    public $eventId;
    /**
     * @var integer
     * @persistent
     */
    public $id;
    /**
     * @var TransitionsFactory
     */
    private $transitionsFactory;
    /**
     * @var \ServiceEvent
     */
    private $serviceEvent;
    /**
     * @var ModelEvent
     */
    private $event;
    /**
     * @var PriceCalculatorFactory
     */
    private $priceCalculatorFactory;

    /**
     * @var EventPaymentFactory
     */
    private $eventPaymentFactory;
    /**
     * @var \ServiceEventPersonAccommodation
     */
    private $serviceEventPersonAccommodation;
    /**
     * @var \ServiceEventParticipant
     */
    private $serviceEventParticipant;
    /**
     * @var ModelEventPayment
     */
    private $model;
    /**
     * @var Container
     */
    private $container;
    /**
     * @var Machine
     */
    private $machine;
    /**
     * @var SymbolGeneratorFactory
     */
    private $symbolGeneratorFactory;

    public function injectServiceEventPayment(\ServiceEventPayment $serviceEventPayment) {
        $this->serviceEventPayment = $serviceEventPayment;
    }

    public function injectServicePersonFactory(ReferencedPersonFactory $referencedPersonFactory) {
        $this->referencedPersonFactory = $referencedPersonFactory;
    }

    public function injectEventTransitionFactory(TransitionsFactory $transitionsFactory) {
        $this->transitionsFactory = $transitionsFactory;
    }

    public function injectSymbolGeneratorFactory(SymbolGeneratorFactory $symbolGeneratorFactory) {
        $this->symbolGeneratorFactory = $symbolGeneratorFactory;
    }

    public function injectServiceEvent(\ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    public function injectPriceCalculatorFactory(PriceCalculatorFactory $priceCalculatorFactory) {
        $this->priceCalculatorFactory = $priceCalculatorFactory;
    }

    public function injectEventPaymentFactory(EventPaymentFactory $eventPaymentFactory) {
        $this->eventPaymentFactory = $eventPaymentFactory;
    }

    public function injectServiceEventPersonAccommodation(\ServiceEventPersonAccommodation $serviceEventPersonAccommodation) {
        $this->serviceEventPersonAccommodation = $serviceEventPersonAccommodation;
    }

    public function injectServiceEventParticipant(\ServiceEventParticipant $serviceEventParticipant) {
        $this->serviceEventParticipant = $serviceEventParticipant;
    }

    public function injectContainer(Container $container) {
        $this->container = $container;
    }

    /* public function createComponentPaymentGrid() {
         return new PaymentDetailGrid(
             $this->getTranslator(),
             $this->getCalculator(),
             [
                 'accommodated_person_ids' => [94, 95],
                 'event_participants' => [],
             ]
         );
     }*/

    public function getCalculator(): PriceCalculator {
        return $this->priceCalculatorFactory->createCalculator($this->getEvent());
    }

    public function handleCreateForm(Form $form) {
        $values = $form->getValues();
        $price = ['kc' => 251, 'eur' => 11];

        //$calculator = $this->priceCalculatorFactory->createCalculator($this->getEvent());
        //$price = $calculator->execute($values->data);
        /**
         * @var $model ModelEventPayment
         */
        $model = $this->serviceEventPayment->createNew([
            'person_id' => $this->getUser()->getIdentity()->getPerson()->person_id,
            'event_id' => $this->getEvent()->event_id,
            'data' => '',
            'state' => null,
            'price_kc' => $price['kc'],
            'price_eur' => $price['eur'],
        ]);
        $this->serviceEventPayment->save($model);

        foreach ($form->getComponents() as $name => $component) {
            if ($form->isSubmitted() === $component) {
                $model->executeTransition($this->getMachine(), $name);
            }
        }
        $this->redirect('confirm', ['id' => $model->payment_id]);
    }

    public function createComponentCreateForm() {
        $control = $this->eventPaymentFactory->createCreateForm();
        $form = $control->getForm();

        // inject transitions buttons
        $machine = $this->getMachine();
        $transitions = $machine->getAvailableTransitions();
        foreach ($transitions as $transition) {
            $button = $form->addSubmit($transition->getId(), $transition->getLabel());
            $button->getControlPrototype()->class .= 'btn btn-' . $transition->getType();
        }
        $form->onSuccess[] = function (Form $form) {
            $this->handleCreateForm($form);
        };
        return $control;
    }

    private function getModel(): ModelEventPayment {
        if (!$this->model) {
            $row = $this->serviceEventPayment->findByPrimary($this->id);
            $this->model = ModelEventPayment::createFromTableRow($row);

        }
        return $this->model;
    }

    public function createComponentConfirmForm() {
        $control = new DetailControl($this->getTranslator(), $this->getCalculator(), $this->getModel());
        $form = $control->getFormControl()->getForm();

        // inject transitions buttons
        $machine = $this->getMachine();
        $machine->setState($this->getModel()->state);
        $transitions = $machine->getAvailableTransitions();
        foreach ($transitions as $transition) {
            $button = $form->addSubmit($transition->getId(), $transition->getLabel());
            $button->getControlPrototype()->class .= 'btn btn-' . $transition->getType();
        }


        $form->onSuccess[] = function (Form $form) {
            $generator = $this->getSymbolGenerator();

            foreach ($form->getComponents() as $name => $component) {
                if ($form->isSubmitted() === $component) {
                    if ($name === 'edit') {
                        $this->redirect('edit');
                    } else {
                        $model = $this->getModel();
                        $model->update($generator->crate($model));
                        $this->serviceEventPayment->save($model);
                        $model->executeTransition($this->getMachine(), $name);
                        $this->redirect('detail');
                    }
                }
            }
        };
        return $control;
    }

    private function getSymbolGenerator(): AbstractSymbolGenerator {
        return $this->symbolGeneratorFactory->createGenerator($this->getEvent());
    }

    private function getEvent() {
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

    private function getMachine(): Machine {
        if (!$this->machine) {
            $this->machine = $this->transitionsFactory->setUpMachine($this->getEvent());
        }
        return $this->machine;
    }

}
