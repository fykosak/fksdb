<?php

namespace EventModule;

use FKSDB\Components\Controls\Payment\DetailControl;
use FKSDB\Components\Factories\PaymentFactory as PaymentComponentFactory;
use FKSDB\Components\Forms\Controls\Payment\SelectForm;
use FKSDB\Components\Grids\Payment\OrgPaymentGrid;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\Payment\Transition\PaymentMachine;
use FKSDB\Transitions\Machine;
use FKSDB\Transitions\MachineFactory;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\NotImplementedException;

/**
 * Class PaymentPresenter
 * @package EventModule
 */
class PaymentPresenter extends BasePresenter {

    /**
     * @var integer
     * @persistent
     */
    public $id;

    /**
     * @var \FKSDB\ORM\Models\ModelPayment
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
     * @var MachineFactory
     */
    private $machineFactory;

    /**
     * @var PaymentComponentFactory
     */
    private $paymentComponentFactory;

    /**
     * @param \ServicePayment $servicePayment
     */
    public function injectServicePayment(\ServicePayment $servicePayment) {
        $this->servicePayment = $servicePayment;
    }

    /**
     * @param MachineFactory $machineFactory
     */
    public function injectMachineFactory(MachineFactory $machineFactory) {
        $this->machineFactory = $machineFactory;
    }

    /**
     * @param PaymentComponentFactory $paymentComponentFactory
     */
    public function injectPaymentComponentFactory(PaymentComponentFactory $paymentComponentFactory) {
        $this->paymentComponentFactory = $paymentComponentFactory;
    }
    /* ********* titles *****************/
    /**
     *
     */
    public function titleCreate() {
        $this->setTitle(_('New payment'));
        $this->setIcon('fa fa-credit-card');
    }

    /**
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws \Nette\Application\AbortException
     */
    public function titleEdit() {
        $this->setTitle(\sprintf(_('Edit payment #%s'), $this->getModel()->getPaymentId()));
        $this->setIcon('fa fa-credit-card');
    }

    /**
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws \Nette\Application\AbortException
     */
    public function titleDetail() {
        $this->setTitle(\sprintf(_('Payment detail #%s'), $this->getModel()->getPaymentId()));
        $this->setIcon('fa fa-credit-card');
    }

    /**
     *
     */
    public function titleList() {
        $this->setTitle(_('List of payments'));
        $this->setIcon('fa fa-credit-card');
    }
    /* ********* Authorization *****************/
    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function authorizedDetail() {
        if (!$this->hasApi()) {
            $this->setAuthorized(false);
            return;
        }
        $this->setAuthorized($this->isContestsOrgAllowed($this->getModel(), 'detail'));
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function authorizedEdit() {
        if (!$this->hasApi()) {
            $this->setAuthorized(false);
            return;
        }
        $this->setAuthorized($this->canEdit());
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function authorizedCreate() {
        if (!$this->hasApi()) {
            $this->setAuthorized(false);
            return;
        }
        $this->setAuthorized($this->isContestsOrgAllowed('event.payment', 'create'));
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function authorizedList() {
        if (!$this->hasApi()) {
            $this->setAuthorized(false);
            return;
        }
        $this->setAuthorized($this->isContestsOrgAllowed('event.payment', 'list'));
    }

    /**
     * Is org or (is own payment and can edit)
     * @return bool
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    private function canEdit(): bool {
        return ($this->getModel()->canEdit() && $this->isContestsOrgAllowed($this->getModel(), 'edit')) ||
            $this->isOrg();
    }

    /**
     * @return bool
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    private function isOrg(): bool {
        return $this->isContestsOrgAllowed('event.payment', 'org');
    }

    /**
     * @return \FKSDB\ORM\Models\ModelPayment
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws \Nette\Application\AbortException
     */
    private function getModel(): ModelPayment {
        if (!$this->model) {
            $row = $this->servicePayment->findByPrimary($this->id);
            if (!$row) {
                throw new BadRequestException(_('Payment does not exists'), 404);
            }
            $this->model = ModelPayment::createFromTableRow($row);
            $this->model->getRelatedPersonAccommodation();
            if ($this->model->event_id !== $this->getEvent()->event_id) {
                throw new ForbiddenRequestException(_('Payment does not belong to this event'), 403);
            }
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
            throw new \InvalidArgumentException(_('Expected class PaymentMachine'), 500);
        }
        return $this->machine;
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
    /* ********* startup *****************/
    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    protected function startup() {
        parent::startup();
        // protection not implements eventPayment
        if (!$this->hasApi()) {
            $this->flashMessage(_('Event has not payment API'));
            $this->redirect(':Event:Dashboard:default');
        };
    }
    /* ********* actions *****************/
    /**
     * @param $id
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function actionEdit($id) {
        if (!$this->canEdit()) {
            $this->flashMessage(\sprintf(_('Payment #%s can not be edited'), $this->getModel()->getPaymentId()), \BasePresenter::FLASH_ERROR);
            $this->redirect(':MyPayments:');
        }
        /**
         * @var SelectForm $component
         */
        $component = $this->getComponent('form');
        $component->setModel($this->getModel());
    }

    /**
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function actionCreate() {
        if ((\count($this->getMachine()->getAvailableTransitions(null)) === 0)) {
            $this->flashMessage(_('Payment is not allowed in this time!'));
            if (!$this->isOrg()) {
                $this->redirect('Dashboard:default');
            }
        };
    }
    /* ********* render *****************/
    /**
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws \Nette\Application\AbortException
     */
    public function renderEdit() {
        $this->template->model = $this->getModel();
    }
    /* ********* Components *****************/
    /**
     * @return DetailControl
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    protected function createComponentDetailControl(): DetailControl {
        return $this->paymentComponentFactory->createDetailControl($this->getModel(), $this->getMachine());
    }

    /**
     * @return OrgPaymentGrid
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    protected function createComponentOrgGrid(): OrgPaymentGrid {
        return $this->paymentComponentFactory->createOrgGrid($this->getEvent());
    }

    /**
     * @return SelectForm
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    protected function createComponentForm(): SelectForm {
        return $this->paymentComponentFactory->creteForm($this->getEvent(), $this->isOrg(), $this->getMachine());
    }
}
