<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Transitions\TransitionButtonsControl;
use FKSDB\Components\Forms\Controls\Payment\SelectForm;
use FKSDB\Components\Grids\Payment\OrgPaymentGrid;
use FKSDB\Config\Extensions\PaymentExtension;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServicePayment;
use FKSDB\Payment\Transition\PaymentMachine;
use FKSDB\Transitions\Machine;
use FKSDB\UI\PageTitle;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\IResource;

/**
 * Class PaymentPresenter
 * *
 * @method ModelPayment getEntity
 */
class PaymentPresenter extends BasePresenter {
    use EventEntityPresenterTrait;

    /**
     * @var Machine
     */
    private $machine;

    /**
     * @var ServicePayment
     */
    private $servicePayment;

    /**
     * @param ServicePayment $servicePayment
     * @return void
     */
    public function injectServicePayment(ServicePayment $servicePayment) {
        $this->servicePayment = $servicePayment;
    }

    /* ********* titles *****************/
    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleCreate() {
        $this->setPageTitle(new PageTitle(_('New payment'), 'fa fa-credit-card'));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleEdit() {
        $this->setPageTitle(new PageTitle(\sprintf(_('Edit payment #%s'), $this->getEntity()->getPaymentId()), 'fa fa-credit-card'));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleDetail() {
        $this->setPageTitle(new PageTitle(\sprintf(_('Payment detail #%s'), $this->getEntity()->getPaymentId()), 'fa fa-credit-card'));
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleList() {
        $this->setPageTitle(new PageTitle(_('List of payments'), 'fa fa-credit-card'));
    }

    protected function isEnabled(): bool {
        return $this->hasApi();
    }
    /* ********* Authorization *****************/

    /**
     * @param IResource|string|null $resource
     * @param string $privilege
     * @return bool
     * @throws BadRequestException
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->isContestsOrgAuthorized($resource, $privilege);
    }

    /* ********* actions *****************/

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function actionEdit() {
        if (!$this->isContestsOrgAuthorized($this->getEntity(), 'edit')) {
            $this->flashMessage(\sprintf(_('Payment #%s can not be edited'), $this->getEntity()->getPaymentId()), \FKSDB\Modules\Core\BasePresenter::FLASH_ERROR);
            $this->redirect(':Core:MyPayments:');
        }
        $this->traitActionEdit();
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function actionCreate() {
        if (\count($this->getMachine()->getAvailableTransitions(null)) === 0) {
            $this->flashMessage(_('Payment is not allowed in this time!'));
            if (!$this->isOrg()) {
                $this->redirect(':Core:Dashboard:default');
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
     * @throws AbortException
     * @throws BadRequestException
     */
    public function renderDetail() {
        $payment = $this->getEntity();
        $this->template->items = $this->getMachine()->getPriceCalculator()->getGridItems($payment);
        $this->template->model = $payment;
        $this->template->isOrg = $this->isOrg();
    }

    /**
     * @return bool
     * @throws BadRequestException
     * TODO!!!!
     */
    private function isOrg(): bool {
        return $this->isContestsOrgAuthorized($this->getModelResource(), 'org');
    }

    /**
     * @return PaymentMachine
     * @throws AbortException
     * @throws BadRequestException
     * @throws BadTypeException
     */
    private function getMachine(): PaymentMachine {
        if (!$this->machine) {
            $this->machine = $this->getContext()->getService('payment.' . PaymentExtension::MACHINE_PREFIX . $this->getEvent()->event_id);
        }
        if (!$this->machine instanceof PaymentMachine) {
            throw new BadTypeException(PaymentMachine::class, $this->machine);
        }
        return $this->machine;
    }

    private function hasApi(): bool {
        try {
            $this->getMachine();
        } catch (\Exception $exception) {
            return false;
        }
        return true;
    }

    protected function getORMService(): ServicePayment {
        return $this->servicePayment;
    }
    /* ********* Components *****************/
    /**
     * @return TransitionButtonsControl
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function createComponentTransitionButtons(): TransitionButtonsControl {
        return new TransitionButtonsControl($this->getMachine(), $this->getContext(), $this->getEntity());
    }

    /**
     * @return OrgPaymentGrid
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function createComponentGrid(): OrgPaymentGrid {
        return new OrgPaymentGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @return SelectForm
     * @throws AbortException
     * @throws BadRequestException
     * @throws BadTypeException
     */
    protected function createComponentCreateForm(): SelectForm {
        return new SelectForm(
            $this->getContext(),
            $this->isOrg(),
            $this->getMachine(),
            true
        );
    }

    /**
     * @return SelectForm
     * @throws AbortException
     * @throws BadRequestException
     * @throws BadTypeException
     */
    protected function createComponentEditForm(): SelectForm {
        return new SelectForm(
            $this->getContext(),
            $this->isOrg(),
            $this->getMachine(),
            false
        );
    }
}
