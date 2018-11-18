<?php

namespace EventModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Controls\EventPayment\DetailControl;
use FKSDB\Components\Forms\Factories\EventPaymentFactory;
use FKSDB\Components\Grids\EventPayment\OrgEventPaymentGrid;
use FKSDB\EventPayment\PriceCalculator\Price;
use FKSDB\EventPayment\PriceCalculator\PriceCalculator;
use FKSDB\EventPayment\PriceCalculator\PriceCalculatorFactory;
use FKSDB\EventPayment\SymbolGenerator\AbstractSymbolGenerator;
use FKSDB\EventPayment\SymbolGenerator\SymbolGeneratorFactory;
use FKSDB\EventPayment\Transition\Machine;
use FKSDB\EventPayment\Transition\TransitionsFactory;
use FKSDB\ORM\ModelEventPayment;
use Nette\Application\UI\Form;
use Nette\NotImplementedException;

class PaymentPresenter extends BasePresenter {
    /**
     * @var \ServiceEventPayment
     */
    private $serviceEventPayment;
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

    public function injectPriceCalculatorFactory(PriceCalculatorFactory $priceCalculatorFactory) {
        $this->priceCalculatorFactory = $priceCalculatorFactory;
    }

    public function injectEventPaymentFactory(EventPaymentFactory $eventPaymentFactory) {
        $this->eventPaymentFactory = $eventPaymentFactory;
    }

    public function titleCreate() {
        $this->setTitle(\sprintf(_('Nová platba akcie %s'), $this->getEvent()->name));
        $this->setIcon('fa fa-credit-card');
    }

    public function titleEdit() {
        $this->setTitle(\sprintf(_('Úprava platby #%s'), $this->getModel()->getPaymentId()));
        $this->setIcon('fa fa-credit-card');
    }

    public function titleDetail() {
        $this->setTitle(\sprintf(_('Payment detail #%s'), $this->getModel()->getPaymentId()));
        $this->setIcon('fa fa-credit-card');
    }

    public function titleList() {
        $this->setTitle(\sprintf(_('List of payments')));
        $this->setIcon('fa fa-credit-card');
    }

    public function authorizedDetail() {
        return $this->setAuthorized($this->isContestsOrgAllowed($this->getModel(), 'detail'));
    }

    public function authorizedEdit() {
        return $this->setAuthorized($this->isContestsOrgAllowed($this->getModel(), 'edit'));
    }

    public function authorizedCreate() {
        return $this->setAuthorized($this->isContestsOrgAllowed('event.payment', 'create'));
    }

    public function authorizedList() {
        return $this->setAuthorized($this->isContestsOrgAllowed('event.payment', 'list'));
    }

    private function getModel(): ModelEventPayment {
        if (!$this->model) {
            $row = $this->serviceEventPayment->findByPrimary($this->id);
            $this->model = ModelEventPayment::createFromTableRow($row);
        }
        return $this->model;
    }

    public function getPriceCalculator(): PriceCalculator {
        return $this->priceCalculatorFactory->createCalculator($this->getEvent(), $this->getModel()->currency);
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

    public function startup() {
        parent::startup();
        // protection not implements eventPayment
        try {
            $this->getMachine();
        } catch (NotImplementedException $e) {
            $this->flashMessage('Event has not payment API');
            $this->redirect(':Event:Dashboard:default');
        }


    }

    public function handleCreateForm(Form $form) {
        $values = $form->getValues();
        $price = new Price(0, $values->currency);

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
            'price' => $price->getAmount(),
            'currency' => $price->getCurrency(),
        ]);
        $this->serviceEventPayment->save($model);

        foreach ($form->getComponents() as $name => $component) {
            if ($form->isSubmitted() === $component) {
                $model->executeTransition($this->getMachine(), $name, false);
                $this->redirect('detail', ['id' => $model->payment_id]);
            }
        }
    }

    public function createComponentCreateForm(): FormControl {
        $control = $this->eventPaymentFactory->createCreateForm($this->getMachine(), false);
        $control->getForm()->onSuccess[] = function (Form $form) {
            $this->handleCreateForm($form);
        };
        return $control;
    }

    public function actionEdit() {
        if ($this->getModel()->canEdit() || $this->isContestsOrgAllowed($this->getModel(), 'org.edit')) {
            $this['editForm']->getForm()->setDefaults($this->getModel());
        } else {
            $this->flashMessage(\sprintf(_('Platba #%s sa nedá editvať'), $this->getModel()->getPaymentId()), 'danger');
            $this->redirect(':MyPayment:');
        }
    }

    public function handleEditForm(Form $form) {
        $values = $form->getValues();

        $price = new Price();
        $price->setCurrency($values->currency);

        //$calculator = $this->priceCalculatorFactory->createCalculator($this->getEvent());
        //$price = $calculator->execute($values->data);
        /**
         * @var $model ModelEventPayment
         */
        $model = $this->getModel();
        $model->update([
            'data' => '',
            'price' => $price->getAmount(),
            'currency' => $price->getCurrency(),
        ]);
        $this->serviceEventPayment->save($model);
        $this->redirect('detail', ['id' => $model->payment_id]);
    }


    public function handleDetailForm(Form $form) {
        $generator = $this->getSymbolGenerator();

        foreach ($form->getComponents() as $name => $component) {
            if ($form->isSubmitted() === $component) {
                if ($name === 'edit') {
                    $this->redirect('edit');
                } else {
                    $model = $this->getModel();
                    $model->update($generator->crate($model));
                    $this->serviceEventPayment->save($model);
                    $model->executeTransition($this->getMachine(), $name, false);
                    $this->redirect('detail');
                }
            }
        }
    }

    public function createComponentDetailControl(): DetailControl {
        $machine = $this->getMachine();
        $control = $this->eventPaymentFactory->createDetailControl($this->getModel(), $this->getPriceCalculator(), $this->getTranslator(), $machine, false);
        $form = $control->getFormControl()->getForm();

        $form->onSuccess[] = function (Form $form) {
            $this->handleDetailForm($form);
        };
        return $control;
    }

    protected function createComponentOrgGrid(): OrgEventPaymentGrid {
        return new OrgEventPaymentGrid($this->getMachine(), $this->serviceEventPayment, $this->transitionsFactory, $this->eventId);
    }

    public function createComponentEditForm(): FormControl {
        $control = $this->eventPaymentFactory->createEditForm($this->isContestsOrgAllowed($this->getModel(), 'org.edit'));
        $control->getForm()->onSuccess[] = function (Form $form) {
            $this->handleEditForm($form);
        };
        return $control;
    }
}
