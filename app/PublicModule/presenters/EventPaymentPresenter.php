<?php

namespace PublicModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Controls\EventPayment\DetailControl;
use FKSDB\Components\Forms\Factories\EventPaymentFactory;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Components\Grids\Payment\MyPaymentGrid;
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

    public function injectContainer(Container $container) {
        $this->container = $container;
    }

    public function titleNew() {
        $this->setTitle(\sprintf(_('Nová platba akcie %s'), $this->getEvent()->name));
        $this->setIcon('fa fa-credit-card');
    }

    public function titleList() {
        $this->setTitle(_('My payment'));
        $this->setIcon('fa fa-credit-card');
    }

    public function titleEdit() {
        $this->setTitle(\sprintf(_('Úprava platby #%s'), $this->getModel()->getPaymentId()));
        $this->setIcon('fa fa-credit-card');
    }

    public function titleDetail() {
        $this->setTitle(\sprintf(_('Detail platby #%s'), $this->getModel()->getPaymentId()));
        $this->setIcon('fa fa-credit-card');
        $this->setSubtitle(\sprintf('%s', _($this->getModel()->state)));
    }

    public function titleConfirm() {
        $this->setTitle(\sprintf(_('Sumarizácia platby #%s'), $this->getModel()->getPaymentId()));
        $this->setIcon('fa fa-credit-card');
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

    private function getModel(): ModelEventPayment {
        if (!$this->model) {
            $row = $this->serviceEventPayment->findByPrimary($this->id);
            $this->model = ModelEventPayment::createFromTableRow($row);

        }
        return $this->model;
    }

    public function getCalculator(): PriceCalculator {
        return $this->priceCalculatorFactory->createCalculator($this->getEvent());
    }

    private function getSymbolGenerator(): AbstractSymbolGenerator {
        return $this->symbolGeneratorFactory->createGenerator($this->getEvent());
    }

    private function getMachine(): Machine {
        if (!$this->machine) {
            $this->machine = $this->transitionsFactory->setUpMachine($this->getEvent());
        }
        return $this->machine;
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
                $this->redirect('confirm', ['id' => $model->payment_id]);
            }
        }
    }

    public function createComponentCreateForm(): FormControl {
        $control = $this->eventPaymentFactory->createCreateForm($this->getMachine());
        $control->getForm()->onSuccess[] = function (Form $form) {
            $this->handleCreateForm($form);
        };
        return $control;
    }

    public function actionEdit() {
        if (!$this->getModel()->canEdit()) {
            $this->flashMessage(\sprintf(_('Platba #%s sa nedá editvať'), $this->getModel()->getPaymentId()), 'danger');

            $this->redirect('list');
        }
        $this['editForm']->getForm()->setDefaults($this->getModel());
    }

    public function handleEditForm(Form $form) {
        $values = $form->getValues();
        $price = ['kc' => 251, 'eur' => 11];

        //$calculator = $this->priceCalculatorFactory->createCalculator($this->getEvent());
        //$price = $calculator->execute($values->data);
        /**
         * @var $model ModelEventPayment
         */
        $model = $this->getModel();
        $model->update([
            'data' => '',
            'state' => null,
            'price_kc' => $price['kc'],
            'price_eur' => $price['eur'],
        ]);
        $this->serviceEventPayment->save($model);
        $this->redirect('confirm', ['id' => $model->payment_id]);
    }

    public function createComponentEditForm(): FormControl {
        $control = $this->eventPaymentFactory->createEditForm(false);
        $control->getForm()->onSuccess[] = function (Form $form) {
            $this->handleEditForm($form);
        };
        return $control;
    }

    public function actionConfirm() {
        if ($this->getModel()->hasGeneratedSymbols()) {
            $this->redirect('detail');
        }
    }

    public function handleConfirmForm(Form $form) {
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
    }

    public function createComponentConfirmControl(): DetailControl {
        $machine = $this->getMachine();
        $machine->setState($this->getModel()->state);
        $control = $this->eventPaymentFactory->createConfirmControl($this->getModel(), $this->getCalculator(), $this->getTranslator(), $machine);
        $form = $control->getFormControl()->getForm();

        $form->onSuccess[] = function (Form $form) {
            $this->handleConfirmForm($form);
        };
        return $control;
    }

    public function createComponentMyPaymentGrid(): MyPaymentGrid {
        return new MyPaymentGrid($this->serviceEventPayment);
    }

    public function createComponentDetailControl(): DetailControl {
        $machine = $this->getMachine();
        $machine->setState($this->getModel()->state);
        return $this->eventPaymentFactory->createDetailControl($this->getModel(), $this->getCalculator(), $this->getTranslator(), $machine);
    }
}
