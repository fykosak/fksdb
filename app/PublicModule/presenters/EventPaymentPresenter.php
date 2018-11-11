<?php

namespace PublicModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Factories\EventPaymentFactory;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Components\Grids\Payment\PaymentDetailGrid;
use FKSDB\EventPayment\PriceCalculator\PriceCalculatorFactory;
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
     * @var Container
     */
    private $container;

    public function injectServiceEventPayment(\ServiceEventPayment $serviceEventPayment) {
        $this->serviceEventPayment = $serviceEventPayment;
    }

    public function injectServicePersonFactory(ReferencedPersonFactory $referencedPersonFactory) {
        $this->referencedPersonFactory = $referencedPersonFactory;
    }

    public function injectEventTransitionFactory(TransitionsFactory $transitionsFactory) {
        $this->transitionsFactory = $transitionsFactory;
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

    public function createComponentPaymentGrid($name) {
        return new PaymentDetailGrid(
            $this->getTranslator(),
            $this->getModel(),
            $this->getEvent(),
            [
                'accommodated_person_ids' => [94, 95],
                ''
            ],
            $this->serviceEventPersonAccommodation,
            $this->serviceEventParticipant
        );
    }

    public function createComponentCreateForm() {
        $control = $this->eventPaymentFactory->createCreateForm();
        // inject transitions buttons
        $machine = $this->getMachine();
        $form = $control->getForm();
        $transitions = $machine->getAvailableTransitions();
        foreach ($transitions as $transition) {
            $button = $form->addSubmit($transition->getId(), $transition->getLabel());
            $button->getControlPrototype()->class .= 'btn btn-' . $transition->getType();
        }


        $form->onSuccess[] = function (Form $form) {
            $values = $form->getValues();
            $price = ['kc' => 251, 'eur' => 11];
            // $price = $calculator = $this->priceCalculatorFactory->createCalculator($this->getEvent());
            //  $calculator->execute($values->data);
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
                'constant_symbol' => 1234,
                'variable_symbol' => 1234,
                'specific_symbol' => 1234,
            ]);
            $this->serviceEventPayment->save($model);

            foreach ($form->getComponents() as $name => $component) {
                if ($form->isSubmitted() === $component) {
                    $model->executeTransition($this->getMachine(), $name);
                }
            }
            $this->redirect('confirm', ['id' => $model->payment_id]);
        };
        return $control;
    }

    private function getModel(): ModelEventPayment {
        $row = $this->serviceEventPayment->findByPrimary($this->id);
        return ModelEventPayment::createFromTableRow($row);
    }

    public function actionConfirm() {
        /**
         * @var $control FormControl
         */
        $control = $this['confirmForm'];
        $control->getForm()->setDefaults($this->getModel()->toArray());
    }

    public function createComponentConfirmForm() {
        $control = $this->eventPaymentFactory->createConfirmForm($this->getModel()->getPerson());
        // inject transitions buttons
        $machine = $this->getMachine();
        $form = $control->getForm();
        $transitions = $machine->getAvailableTransitions();
        foreach ($transitions as $transition) {
            $button = $form->addSubmit($transition->getId(), $transition->getLabel());
            $button->getControlPrototype()->class .= 'btn btn-' . $transition->getType();
        }


        $form->onSuccess[] = function (Form $form) {
            $values = $form->getValues();

            $price = ['kc' => 251, 'eur' => 11];
            // $price = $calculator = $this->priceCalculatorFactory->createCalculator($this->getEvent());
            //  $calculator->execute($values->data);
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
                'constant_symbol' => 1234,
                'variable_symbol' => 1234,
                'specific_symbol' => 1234,
            ]);
            $this->serviceEventPayment->save($model);

            foreach ($form->getComponents() as $name => $component) {
                if ($form->isSubmitted() === $component) {
                    $model->executeTransition($this->getMachine(), $name);
                }
            }
            $this->redirect('confirm');
        };
        return $control;
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
        return $this->transitionsFactory->setUpMachine($this->getEvent());
    }

}
