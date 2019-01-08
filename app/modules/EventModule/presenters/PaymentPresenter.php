<?php

namespace EventModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Controls\Payment\DetailControl;
use FKSDB\Components\Forms\Factories\PaymentFactory;
use FKSDB\Components\Grids\Payment\OrgPaymentGrid;
use FKSDB\ORM\ModelPayment;
use FKSDB\ORM\Services\ServicePaymentAccommodation;
use FKSDB\Payment\Handler\DuplicateAccommodationPaymentException;
use FKSDB\Payment\Handler\EmptyDataException;
use FKSDB\Payment\Transition\PaymentMachine;
use FKSDB\Transitions\Machine;
use FKSDB\Transitions\MachineFactory;
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
    private $servicePayment;

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

    public function injectServicePayment(\ServicePayment $servicePayment) {
        $this->servicePayment = $servicePayment;
    }

    public function injectServicePaymentAccommodation(ServicePaymentAccommodation $servicePaymentAccommodation) {
        $this->servicePaymentAccommodation = $servicePaymentAccommodation;
    }

    public function injectMachineFactory(MachineFactory $machineFactory) {
        $this->machineFactory = $machineFactory;
    }

    public function injectPaymentFactory(PaymentFactory $eventPaymentFactory) {
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
        return ($this->getModel()->canEdit() && $this->isContestsOrgAllowed($this->getModel(), 'edit')) ||
            $this->isContestsOrgAllowed($this->getModel(), 'org');
    }

    /**
     * @return ModelPayment
     */
    private function getModel(): ModelPayment {
        if (!$this->model) {
            $row = $this->servicePayment->findByPrimary($this->id);
            $this->model = ModelPayment::createFromTableRow($row);
            $this->model->getRelatedPersonAccommodation();
        }
        return $this->model;
    }

    protected function isOrg(): bool {
        return $this->isContestsOrgAllowed('event.payment', 'org');
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
     * @return FormControl
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function createComponentCreateForm(): FormControl {
        $control = $this->eventPaymentFactory->createCreateForm($this->getEvent());
        $control->getForm()->onSuccess[] = function (Form $form) {
            $this->handleSubmit($form, true);
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
            $formControl = $this->getComponent('editForm');
            $values = $this->getModel()->toArray();
            $values['payment_accommodation'] = $this->serializePaymentAccommodation();
            $formControl->getForm()->setDefaults($values);
        } else {
            $this->flashMessage(\sprintf(_('Platba #%s sa nedá editvať'), $this->getModel()->getPaymentId()), 'danger');
            $this->redirect(':MyPayment:');
        }
    }

    public function actionCreate() {
        if ((\count($this->getMachine()->getAvailableTransitions(null)) === 0) && !$this->isOrg()) {
            $this->flashMessage(_('Platby niesu spustené!'));
            $this->redirect('Dashboard:default');
        };
    }

    public function renderEdit() {
        $this->template->model = $this->getModel();
    }

    private function serializePaymentAccommodation() {
        $query = $this->getModel()->getRelatedPersonAccommodation();
        $items = [];
        foreach ($query as $row) {
            $items[$row->event_person_accommodation_id] = true;
        }
        return \json_encode($items);
    }

    private function handleSubmit(Form $form, bool $create) {
        $values = $form->getValues();
        if ($create) {
            $model = $this->servicePayment->createNew([
                'person_id' => $this->getUser()->getIdentity()->getPerson()->person_id,
                'event_id' => $this->getEvent()->event_id,
                'state' => $this->getMachine()->getInitState(),
            ]);

        } else {
            $model = $this->getModel();
        }
        $this->servicePayment->updateModel($model, [
            'currency' => $values->currency,
            'person_id' => $values->offsetExists('person_id') ? $values->person_id : $model->person_id,
        ]);
        $this->servicePayment->save($model);

        $connection = $this->servicePayment->getConnection();
        $connection->beginTransaction();

        try {
            $this->servicePaymentAccommodation->prepareAndUpdate($values->payment_accommodation, $model);
        } catch (DuplicateAccommodationPaymentException $e) {
            $this->flashMessage($e->getMessage(), 'danger');
            $connection->rollBack();
            return;
        } catch (EmptyDataException $e) {
            $this->flashMessage($e->getMessage(), 'danger');
            $connection->rollBack();
            return;
        }
        $connection->commit();

        $this->flashMessage($create ? _('Platba bola vytvorená') : _('Platba bola upravená'));
        $this->redirect('detail', ['id' => $model->payment_id]);
    }

    /**
     * @return DetailControl
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function createComponentDetailControl(): DetailControl {
        return $this->eventPaymentFactory->createDetailControl($this->getModel(), $this->getTranslator(), $this->getMachine());
    }

    /**
     * @return OrgPaymentGrid
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    protected function createComponentOrgGrid(): OrgPaymentGrid {
        return new OrgPaymentGrid($this->getMachine(), $this->servicePayment, $this->getEvent());
    }

    /**
     * @return FormControl
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function createComponentEditForm(): FormControl {
        $control = $this->eventPaymentFactory->createEditForm($this->isContestsOrgAllowed($this->getModel(), 'org.edit'), $this->getEvent());
        $control->getForm()->onSuccess[] = function (Form $form) {
            $this->handleSubmit($form, false);
        };
        return $control;
    }
}
