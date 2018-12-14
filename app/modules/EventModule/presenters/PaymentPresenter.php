<?php

namespace EventModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Controls\EventPayment\DetailControl;
use FKSDB\Components\Forms\Factories\PaymentFactory;
use FKSDB\Components\Grids\EventPayment\OrgEventPaymentGrid;
use FKSDB\EventPayment\Handler\DuplicateAccommodationPaymentException;
use FKSDB\EventPayment\Handler\EmptyDataException;
use FKSDB\EventPayment\SymbolGenerator\AlreadyGeneratedSymbolsException;
use FKSDB\EventPayment\Transition\Machine;
use FKSDB\EventPayment\Transition\MachineFactory;
use FKSDB\EventPayment\Transition\PaymentMachine;
use FKSDB\ORM\ModelPayment;
use FKSDB\ORM\Services\ServicePaymentAccommodation;
use Nette\Application\UI\Form;
use Nette\NotImplementedException;

class PaymentPresenter extends BasePresenter {

    /**
     * @var integer
     * @persistent
     */
    public $id;

    /**
     * @var ModelPayment
     */
    private $model;

    /**
     * @var Machine
     */
    private $machine;

    /**
     * @var \ServicePayment
     */
    private $serviceEventPayment;

    /**
     * @var PaymentFactory
     */
    private $eventPaymentFactory;

    /**
     * @var MachineFactory
     */
    private $machineFactory;
    /**
     * @var ServicePaymentAccommodation
     */
    private $servicePaymentAccommodation;

    public function injectServiceEventPayment(\ServicePayment $serviceEventPayment) {
        $this->serviceEventPayment = $serviceEventPayment;
    }

    public function injectServicePaymentAccommodation(ServicePaymentAccommodation $servicePaymentAccommodation) {
        $this->servicePaymentAccommodation = $servicePaymentAccommodation;
    }

    public function injectMachineFactory(MachineFactory $machineFactory) {
        $this->machineFactory = $machineFactory;
    }

    public function injectEventPaymentFactory(PaymentFactory $eventPaymentFactory) {
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
        if (!$this->hasApi()) {
            return $this->setAuthorized(false);
        }
        return $this->setAuthorized($this->isContestsOrgAllowed($this->getModel(), 'detail'));
    }

    public function authorizedEdit() {
        if (!$this->hasApi()) {
            return $this->setAuthorized(false);
        }
        return $this->setAuthorized($this->canEdit());
    }

    public function authorizedCreate() {
        if (!$this->hasApi()) {
            return $this->setAuthorized(false);
        }
        return $this->setAuthorized($this->isContestsOrgAllowed('event.payment', 'create'));
    }

    public function authorizedList() {
        if (!$this->hasApi()) {
            return $this->setAuthorized(false);
        }
        return $this->setAuthorized($this->isContestsOrgAllowed('event.payment', 'list'));
    }

    /**
     * Is org or (is own payment and can edit)
     * @return bool
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    private function canEdit() {
        return ($this->getModel()->canEdit() && $this->isContestsOrgAllowed($this->getModel(), 'edit')) || $this->isContestsOrgAllowed($this->getModel(), 'org.edit');
    }

    /**
     * @return ModelPayment
     */
    private function getModel(): ModelPayment {
        if (!$this->model) {
            $row = $this->serviceEventPayment->findByPrimary($this->id);
            $this->model = ModelPayment::createFromTableRow($row);
            $this->model->getRelatedPersonAccommodation();
        }
        return $this->model;
    }

    /**
     * @return PaymentMachine
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    private function getMachine(): PaymentMachine {
        if (!$this->machine) {
            $this->machine = $this->machineFactory->setUpMachine($this->getEvent());
        }
        if (!$this->machine instanceof PaymentMachine) {
            throw new \InvalidArgumentException('Očakávaná trieda PaymentMachine');
        }
        return $this->machine;
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function startup() {
        parent::startup();
        // protection not implements eventPayment
        if (!$this->hasApi()) {
            $this->flashMessage('Event has not payment API');
            $this->redirect(':Event:Dashboard:default');
        };
    }

    /**
     * @return bool
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    private function hasApi(): bool {
        try {
            $this->getMachine();
        } catch (NotImplementedException $e) {
            return false;
        }
        return true;
    }

    /**
     * @param Form $form
     * @throws AlreadyGeneratedSymbolsException
     * @throws \FKSDB\EventPayment\Transition\UnavailableTransitionException
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     * @throws \Nette\Application\ForbiddenRequestException
     */
    public function handleCreateForm(Form $form) {
        $values = $form->getValues();
        /**
         * @var $model ModelPayment
         */
        $model = $this->serviceEventPayment->createNew([
            'person_id' => $this->getUser()->getIdentity()->getPerson()->person_id,
            'event_id' => $this->getEvent()->event_id,

            'state' => null,
            'currency' => $values->currency,
        ]);
        $this->serviceEventPayment->save($model);

        try {
            $this->servicePaymentAccommodation->prepareAndUpdate($values->payment_accommodation, $model);
        } catch (DuplicateAccommodationPaymentException $e) {
            $this->flashMessage($e->getMessage());
            $model->delete();
            return;
            // $this->redirect('this');
            // $this->redirect('edit', ['id' => $model->payment_id]);
        } catch (EmptyDataException $e) {
            $this->flashMessage($e->getMessage(), 'danger');
            $model->delete();
            return;
        }

        foreach ($form->getComponents() as $name => $component) {
            if ($form->isSubmitted() === $component) {
                $this->getMachine()->executeTransition($name, $model);
                $this->redirect('detail', ['id' => $model->payment_id]);
            }
        }
        $this->flashMessage('Platba bola zaregistrovaná');

    }

    /**
     * @return FormControl
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function createComponentCreateForm(): FormControl {
        $control = $this->eventPaymentFactory->createCreateForm($this->getMachine(), $this->getEvent());
        $control->getForm()->onSuccess[] = function (Form $form) {
            $this->handleCreateForm($form);
        };
        return $control;
    }

    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function actionEdit($id) {
        if ($this->canEdit()) {
            /**
             * @var $formControl FormControl
             */
            $formControl = $this['editForm'];
            $values = $this->getModel()->toArray();
            $values['payment_accommodation'] = $this->serializePaymentAccommodation();
            $formControl->getForm()->setDefaults($values);
        } else {
            $this->flashMessage(\sprintf(_('Platba #%s sa nedá editvať'), $this->getModel()->getPaymentId()), 'danger');
            $this->redirect(':MyPayment:');
        }
    }

    private function serializePaymentAccommodation() {
        $query = $this->getModel()->getRelatedPersonAccommodation();
        $items = [];
        foreach ($query as $row) {
            $items[$row->event_person_accommodation_id] = true;
        }
        return \json_encode($items);
    }

    /**
     * @param Form $form
     * @throws \Nette\Application\AbortException
     */
    public function handleEditForm(Form $form) {
        $values = $form->getValues();
        $model = $this->getModel();
        $model->update([
            'currency' => $values->currency,
            'person_id' => $values->offsetExists('person_id') ? $values->person_id : $model->person_id,
        ]);

        try {
            $this->servicePaymentAccommodation->prepareAndUpdate($values->payment_accommodation, $model);
        } catch (DuplicateAccommodationPaymentException $e) {
            $this->flashMessage($e->getMessage(), 'danger');
            $this->redirect('this');
        } catch (EmptyDataException $e) {
            $this->flashMessage($e->getMessage(), 'danger');
            $this->redirect('this');
        }
        $this->flashMessage(_('Platba bola upravená'));
        $this->redirect('detail', ['id' => $model->payment_id]);
    }

    /**
     * @param Form $form
     * @throws \FKSDB\EventPayment\Transition\UnavailableTransitionException
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\ForbiddenRequestException
     * @throws \Nette\Application\BadRequestException
     *
     */
    public function handleDetailForm(Form $form) {

        foreach ($form->getComponents() as $name => $component) {
            if ($form->isSubmitted() === $component) {
                if ($name === 'edit') {
                    $this->redirect('edit');
                } else {
                    $model = $this->getModel();
                    try {
                        $this->getMachine()->executeTransition($name, $model);
                        $this->redirect('detail');
                    } catch (AlreadyGeneratedSymbolsException $e) {
                        $this->flashMessage($e->getMessage(), 'danger');
                        $this->redirect('this');
                    }
                }
            }
        }
    }

    /**
     * @return DetailControl
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function createComponentDetailControl(): DetailControl {
        $control = $this->eventPaymentFactory->createDetailControl($this->getModel(), $this->getTranslator(), $this->getMachine());
        $form = $control->getFormControl()->getForm();

        $form->onSuccess[] = function (Form $form) {
            $this->handleDetailForm($form);
        };
        return $control;
    }

    /**
     * @return OrgEventPaymentGrid
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    protected function createComponentOrgGrid(): OrgEventPaymentGrid {
        return new OrgEventPaymentGrid($this->getMachine(), $this->serviceEventPayment, $this->getEvent());
    }

    /**
     * @return FormControl
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function createComponentEditForm(): FormControl {
        $control = $this->eventPaymentFactory->createEditForm($this->isContestsOrgAllowed($this->getModel(), 'org.edit'), $this->getEvent());
        $control->getForm()->onSuccess[] = function (Form $form) {
            $this->handleEditForm($form);
        };
        return $control;
    }
}
