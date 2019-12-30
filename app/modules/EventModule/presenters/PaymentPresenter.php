<?php

namespace EventModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Payment\StateDisplayControl;
use FKSDB\Components\Controls\Transitions\TransitionButtonsControl;
use FKSDB\Components\Factories\PaymentFactory as PaymentComponentFactory;
use FKSDB\Components\Forms\Controls\Payment\SelectForm;
use FKSDB\Components\Grids\Payment\OrgPaymentGrid;
use FKSDB\Config\Extensions\PaymentExtension;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServicePayment;
use FKSDB\Payment\Transition\PaymentMachine;
use FKSDB\Transitions\Machine;
use InvalidArgumentException;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\NotImplementedException;
use function count;
use function sprintf;

/**
 * Class PaymentPresenter
 * @package EventModule
 * @method ModelPayment getEntity
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
    /**
     *
     */
    public function titleCreate() {
        $this->setTitle(_('New payment'));
        $this->setIcon('fa fa-credit-card');
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleEdit() {
        $this->setTitle(sprintf(_('Edit payment #%s'), $this->getEntity()->getPaymentId()));
        $this->setIcon('fa fa-credit-card');
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleDetail() {
        $this->setTitle(sprintf(_('Payment detail #%s'), $this->getEntity()->getPaymentId()));
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
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedDetail() {
        if (!$this->hasApi()) {
            $this->setAuthorized(false);
            return;
        }
        $this->setAuthorized($this->isContestsOrgAllowed($this->getEntity(), 'detail'));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedEdit() {
        if (!$this->hasApi()) {
            $this->setAuthorized(false);
            return;
        }
        $this->setAuthorized($this->canEdit());
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedCreate() {
        if (!$this->hasApi()) {
            $this->setAuthorized(false);
            return;
        }
        $this->setAuthorized($this->isContestsOrgAllowed('event.payment', 'create'));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedList() {
        if (!$this->hasApi()) {
            $this->setAuthorized(false);
            return;
        }
        $this->setAuthorized($this->isContestsOrgAllowed('event.payment', 'list'));
    }
    /* ********* actions *****************/
    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function actionEdit() {
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
    /**
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
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
        // protection not implements eventPayment
        if (!$this->hasApi()) {
            $this->flashMessage(_('Event has not payment API'));
            $this->redirect(':Event:Dashboard:default');
        }
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
     * @return FormControl
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    protected function createComponentEditButtonForm(): FormControl {
        $formControl = new FormControl();
        $form = $formControl->getForm();
        /**
         * @var PaymentPresenter $presenter
         */
        $presenter = $this->getPresenter();
        if ($this->getEntity()->canEdit() || $presenter->getContestAuthorizator()->isAllowed($this->getEntity(), 'org', $this->getEntity()->getEvent()->getContest())) {
            $submit = $form->addSubmit('edit', _('Edit items'));
            $submit->onClick[] = function () {
                $this->getPresenter()->redirect('edit');
            };
        }
        return $formControl;
    }

    /**
     * @return StateDisplayControl
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    protected function createComponentStateDisplay(): StateDisplayControl {
        return new StateDisplayControl($this->translator, $this->getEntity());
    }

    /**
     * @return TransitionButtonsControl
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    protected function createComponentTransitionButtons(): TransitionButtonsControl {
        return new TransitionButtonsControl($this->machine, $this->translator, $this->getEntity());
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
        return $this->isContestsOrgAllowed('event.payment', 'org');
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
     * @throws AbortException
     * @throws BadRequestException
     */
    private function hasApi(): bool {
        try {
            $this->getMachine();
        } catch (NotImplementedException $exception) {
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
}
