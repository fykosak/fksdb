<?php

namespace EventModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Transitions\TransitionButtonsControl;
use FKSDB\Components\Factories\PaymentFactory as PaymentComponentFactory;
use FKSDB\Components\Forms\Controls\Payment\SelectForm;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\Payment\OrgPaymentGrid;
use FKSDB\Config\Extensions\PaymentExtension;
use FKSDB\NotImplementedException;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServicePayment;
use FKSDB\Payment\Transition\PaymentMachine;
use FKSDB\Transitions\Machine;
use InvalidArgumentException;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use function count;
use function sprintf;

/**
 * Class PaymentPresenter
 * @package EventModule
 * @method ModelPayment getEntity
 * @method ModelPayment loadEntity(int $id)
 */
class PaymentPresenter extends BasePresenter {
    use EventEntityTrait;
    /**
     * @var Machine
     */
    private $machine;

    /**
     * @var ServicePayment
     */
    private $servicePayment;

    /**
     * @var PaymentComponentFactory
     */
    private $paymentComponentFactory;

    /**
     * @param ServicePayment $servicePayment
     */
    public function injectServicePayment(ServicePayment $servicePayment) {
        $this->servicePayment = $servicePayment;
    }

    /**
     * @param PaymentComponentFactory $paymentComponentFactory
     */
    public function injectPaymentComponentFactory(PaymentComponentFactory $paymentComponentFactory) {
        $this->paymentComponentFactory = $paymentComponentFactory;
    }

    /* ********* titles *****************/
    public function titleCreate() {
        $this->setTitle(_('New payment'));
        $this->setIcon('fa fa-credit-card');
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleEdit(int $id) {
        $this->setTitle(sprintf(_('Edit payment #%s'), $this->loadEntity($id)->getPaymentId()));
        $this->setIcon('fa fa-credit-card');
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleDetail(int $id) {
        $this->setTitle(sprintf(_('Payment detail #%s'), $this->loadEntity($id)->getPaymentId()));
        $this->setIcon('fa fa-credit-card');
    }

    public function titleList() {
        $this->setTitle(_('List of payments'));
        $this->setIcon('fa fa-credit-card');
    }

    /**
     * @return bool
     */
    protected function isEnabledForEvent(): bool {
        return $this->hasApi();
    }
    /* ********* Authorization *****************/
    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function authorizedDetail(int $id) {
        $this->setAuthorized($this->isContestsOrgAllowed($this->loadEntity($id), 'detail'));
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function authorizedEdit(int $id) {
        $this->loadEntity($id);
        $this->setAuthorized($this->canEdit());
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedCreate() {
        $this->setAuthorized($this->isContestsOrgAllowed('event.payment', 'create'));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedList() {
        $this->setAuthorized($this->isContestsOrgAllowed('event.payment', 'list'));
    }

    /* ********* actions *****************/
    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function actionDetail(int $id) {
        $this->loadEntity($id);
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function actionEdit(int $id) {
        $this->loadEntity($id);
        if (!$this->canEdit()) {
            $this->flashMessage(sprintf(_('Payment #%s can not be edited'), $this->getEntity()->getPaymentId()), \BasePresenter::FLASH_ERROR);
            $this->redirect(':MyPayments:');
        }
        /**
         * @var SelectForm $component
         */
        $component = $this->getComponent('form');
        $component->setModel($this->getEntity());
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function actionCreate() {
        if ((count($this->getMachine()->getAvailableTransitions(null)) === 0)) {
            $this->flashMessage(_('Payment is not allowed in this time!'));
            if (!$this->isOrg()) {
                $this->redirect('Dashboard:default');
            }
        }
    }

    /* ********* render *****************/
    public function renderEdit() {
        $this->template->model = $this->getEntity();
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function renderDetail() {
        $this->template->items = $this->getMachine()->getPriceCalculator()->getGridItems($this->getEntity());
        $this->template->model = $this->getEntity();
        $this->template->isOrg = $this->isOrg();
    }
    /* ********* startup *****************/
    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function startup() {
        parent::startup();
    }
    /* ********* Components *****************/
    /**
     * @return OrgPaymentGrid
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function createComponentOrgGrid(): OrgPaymentGrid {
        return $this->paymentComponentFactory->createOrgGrid($this->getEvent());
    }

    /**
     * @return SelectForm
     * @throws BadRequestException
     * @throws AbortException
     */
    protected function createComponentForm(): SelectForm {
        return $this->paymentComponentFactory->creteForm($this->getEvent(), $this->isOrg(), $this->getMachine());
    }

    /**
     * @return TransitionButtonsControl
     */
    protected function createComponentTransitionButtons(): TransitionButtonsControl {
        return $this->machine->createComponentTransitionButtons($this->getEntity());
    }


    /**
     * Is org or (is own payment and can edit)
     * @return bool
     * @throws AbortException
     * @throws BadRequestException
     */
    private function canEdit(): bool {
        return ($this->getEntity()->canEdit() && $this->isContestsOrgAllowed($this->getEntity(), 'edit')) ||
            $this->isOrg();
    }

    /**
     * @return bool
     * @throws BadRequestException
     * @throws AbortException
     */
    private function isOrg(): bool {
        return $this->isContestsOrgAllowed($this->getModelResource(), 'org');
    }

    /**
     * @return PaymentMachine
     * @throws AbortException
     * @throws BadRequestException
     * @throws \Exception
     */
    private function getMachine(): PaymentMachine {
        if (!$this->machine) {
            $this->machine = $this->context->getService('payment.' . PaymentExtension::MACHINE_PREFIX . $this->getEvent()->event_id);
            if (!$this->machine instanceof PaymentMachine) {
                throw new BadRequestException();
            }
        }
        if (!$this->machine instanceof PaymentMachine) {
            throw new InvalidArgumentException(_('Expected class PaymentMachine'), 500);
        }
        return $this->machine;
    }

    /**
     * @return bool
     */
    private function hasApi(): bool {
        try {
            $this->getMachine();
        } catch (\Exception $exception) {
            return false;
        }
        return true;
    }

    /**
     * @return AbstractServiceSingle
     */
    function getORMService() {
        return $this->servicePayment;
    }

    /**
     * @return string
     */
    protected function getModelResource(): string {
        return ModelPayment::RESOURCE_ID;
    }

    /**
     * @inheritDoc
     */
    public function createComponentGrid(): BaseGrid {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     */
    protected function getCreateForm(): FormControl {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     */
    protected function getEditForm(): FormControl {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     */
    protected function handleCreateFormSuccess(Form $form) {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     */
    protected function handleEditFormSuccess(Form $form) {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function createComponentCreateForm(): Control {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function createComponentEditForm(): Control {
        throw new NotImplementedException();
    }
}
