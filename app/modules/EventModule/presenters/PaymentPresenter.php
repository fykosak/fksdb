<?php

namespace EventModule;

use FKSDB\Components\Controls\Transitions\TransitionButtonsControl;
use FKSDB\Components\Forms\Controls\Payment\SelectForm;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\Payment\OrgPaymentGrid;
use FKSDB\Config\Extensions\PaymentExtension;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServicePayment;
use FKSDB\Payment\Transition\PaymentMachine;
use FKSDB\Transitions\Machine;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;

/**
 * Class PaymentPresenter
 * *
 * @method ModelPayment getEntity
 * @method ModelPayment loadEntity(int $id)
 */
class PaymentPresenter extends BasePresenter {
    use EventEntityTrait;

    private Machine $machine;

    private ServicePayment $servicePayment;

    public function injectServicePayment(ServicePayment $servicePayment): void {
        $this->servicePayment = $servicePayment;
    }

    /* ********* titles *****************/
    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleCreate(): void {
        $this->setTitle(_('New payment'), 'fa fa-credit-card');
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleEdit(int $id): void {
        $this->setTitle(\sprintf(_('Edit payment #%s'), $this->loadEntity($id)->getPaymentId()), 'fa fa-credit-card');
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleDetail(int $id): void {
        $this->setTitle(\sprintf(_('Payment detail #%s'), $this->loadEntity($id)->getPaymentId()), 'fa fa-credit-card');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleList(): void {
        $this->setTitle(_('List of payments'), 'fa fa-credit-card');
    }

    protected function isEnabled(): bool {
        return $this->hasApi();
    }
    /* ********* Authorization *****************/

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function authorizedEdit(int $id): void {
        $this->loadEntity($id);
        $this->setAuthorized($this->canEdit());
    }

    /**
     * @param $resource
     * @param string $privilege
     * @return bool
     * @throws BadRequestException
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->isContestsOrgAuthorized($resource, $privilege);
    }

    /* ********* actions *****************/
    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function actionDetail(int $id): void {
        $this->loadEntity($id);
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function actionEdit(int $id): void {
        $payment = $this->loadEntity($id);
        if (!$this->canEdit()) {
            $this->flashMessage(\sprintf(_('Payment #%s can not be edited'), $payment->getPaymentId()), \BasePresenter::FLASH_ERROR);
            $this->redirect(':MyPayments:');
        }
        /**
         * @var SelectForm $component
         */
        $component = $this->getComponent('form');
        $component->setModel($payment);
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function actionCreate(): void {
        if (\count($this->getMachine()->getAvailableTransitions(null)) === 0) {
            $this->flashMessage(_('Payment is not allowed in this time!'));
            if (!$this->isOrg()) {
                $this->redirect('Dashboard:default');
            }
        }
    }

    /* ********* render *****************/
    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function renderEdit(int $id): void {
        $this->template->model = $this->loadEntity($id);
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function renderDetail(int $id): void {
        $payment = $this->loadEntity($id);
        $this->template->items = $this->getMachine()->getPriceCalculator()->getGridItems($payment);
        $this->template->model = $payment;
        $this->template->isOrg = $this->isOrg();
    }
    /* ********* Components *****************/
    /**
     * @return OrgPaymentGrid
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function createComponentOrgGrid(): OrgPaymentGrid {
        return new OrgPaymentGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @return SelectForm
     * @throws BadRequestException
     * @throws AbortException
     */
    protected function createComponentForm(): SelectForm {
        return new SelectForm(
            $this->getContext(),
            $this->getEvent(),
            $this->isOrg(),
            ['accommodation'],
            $this->getMachine()
        );
    }

    /**
     * @return TransitionButtonsControl
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function createComponentTransitionButtons(): TransitionButtonsControl {
        return new TransitionButtonsControl($this->getMachine(), $this->getContext(), $this->getEntity());
    }


    /**
     * Is org or (is own payment and can edit)
     * @return bool
     * @throws BadRequestException
     */
    private function canEdit(): bool {
        return ($this->getEntity()->canEdit() && $this->isContestsOrgAuthorized($this->getEntity(), 'edit')) ||
            $this->isOrg();
    }

    /**
     * @return bool
     * @throws BadRequestException
     */
    private function isOrg(): bool {
        return $this->isContestsOrgAuthorized($this->getModelResource(), 'org');
    }

    /**
     * @return PaymentMachine
     * @throws AbortException
     * @throws BadRequestException
     * @throws \Exception
     */
    private function getMachine(): PaymentMachine {
        $this->machine ??= $this->getContext()->getService('payment.' . PaymentExtension::MACHINE_PREFIX . $this->getEvent()->event_id);
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

    public function getORMService(): ServicePayment {
        return $this->servicePayment;
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
