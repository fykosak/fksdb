<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Entity\PaymentFormComponent;
use FKSDB\Components\Controls\Transitions\TransitionButtonsControl;
use FKSDB\Components\Grids\Payment\EventPaymentGrid;
use FKSDB\Model\Entity\ModelNotFoundException;
use FKSDB\Model\Events\Exceptions\EventNotFoundException;
use FKSDB\Model\Exceptions\BadTypeException;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use FKSDB\Model\ORM\Models\ModelPayment;
use FKSDB\Model\ORM\Services\ServicePayment;
use FKSDB\Model\Payment\PaymentExtension;
use FKSDB\Model\Payment\Transition\PaymentMachine;
use FKSDB\Model\Transitions\Machine;
use FKSDB\Model\UI\PageTitle;
use Nette\Application\AbortException;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\IResource;

/**
 * Class PaymentPresenter
 * *
 * @method ModelPayment getEntity
 */
class PaymentPresenter extends BasePresenter {
    use EventEntityPresenterTrait;

    private Machine\Machine $machine;

    private ServicePayment $servicePayment;

    final public function injectServicePayment(ServicePayment $servicePayment): void {
        $this->servicePayment = $servicePayment;
    }

    /* ********* titles *****************/
    /**
     * @return void
     * @throws ForbiddenRequestException
     */
    public function titleCreate(): void {
        $this->setPageTitle(new PageTitle(_('New payment'), 'fa fa-credit-card'));
    }

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function titleEdit(): void {
        $this->setPageTitle(new PageTitle(\sprintf(_('Edit payment #%s'), $this->getEntity()->getPaymentId()), 'fa fa-credit-card'));
    }

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function titleDetail(): void {
        $this->setPageTitle(new PageTitle(\sprintf(_('Payment detail #%s'), $this->getEntity()->getPaymentId()), 'fa fa-credit-card'));
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     */
    public function titleList(): void {
        $this->setPageTitle(new PageTitle(_('List of payments'), 'fa fa-credit-card'));
    }

    protected function isEnabled(): bool {
        return $this->hasApi();
    }
    /* ********* Authorization *****************/

    /**
     * @param IResource|string|null $resource
     * @param string|null $privilege
     * @return bool
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool {
        return $this->isContestsOrgAuthorized($resource, $privilege);
    }

    /* ********* actions *****************/

    /**
     * @throws AbortException
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function actionEdit(): void {
        if (!$this->isContestsOrgAuthorized($this->getEntity(), 'edit')) {
            $this->flashMessage(\sprintf(_('Payment #%s can not be edited'), $this->getEntity()->getPaymentId()), \FKSDB\Modules\Core\BasePresenter::FLASH_ERROR);
            $this->redirect(':Core:MyPayments:');
        }
    }

    /**
     *
     * @throws AbortException
     * @throws BadTypeException
     * @throws EventNotFoundException
     */
    public function actionCreate(): void {
        if (\count($this->getMachine()->getAvailableTransitions()) === 0) {
            $this->flashMessage(_('Payment is not allowed in this time!'));
            if (!$this->isOrg()) {
                $this->redirect(':Core:Dashboard:default');
            }
        }
    }

    /* ********* render *****************/
    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function renderEdit(): void {
        $this->template->model = $this->getEntity();
    }

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function renderDetail(): void {
        $payment = $this->getEntity();
        $this->template->items = $this->getMachine()->getPriceCalculator()->getGridItems($payment);
        $this->template->model = $payment;
        $this->template->isOrg = $this->isOrg();
    }

    /**
     * @return bool
     *
     * TODO!!!!
     * @throws EventNotFoundException
     */
    private function isOrg(): bool {
        return $this->isContestsOrgAuthorized($this->getModelResource(), 'org');
    }

    /**
     * @return PaymentMachine
     * @throws BadTypeException
     * @throws EventNotFoundException
     */
    private function getMachine(): PaymentMachine {
        if (!isset($this->machine)) {
            $machine = $this->getContext()->getService('payment.' . PaymentExtension::MACHINE_PREFIX . $this->getEvent()->event_id);
            if (!$machine instanceof PaymentMachine) {
                throw new BadTypeException(PaymentMachine::class, $this->machine);
            }
            $this->machine = $machine;
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
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    protected function createComponentTransitionButtons(): TransitionButtonsControl {
        return new TransitionButtonsControl($this->getMachine(), $this->getContext(), $this->getEntity());
    }

    /**
     * @return EventPaymentGrid
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): EventPaymentGrid {
        return new EventPaymentGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @return PaymentFormComponent
     * @throws BadTypeException
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): PaymentFormComponent {
        return new PaymentFormComponent(
            $this->getContext(),
            $this->isOrg(),
            $this->getMachine(),
            null
        );
    }

    /**
     * @return PaymentFormComponent
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    protected function createComponentEditForm(): PaymentFormComponent {
        return new PaymentFormComponent(
            $this->getContext(),
            $this->isOrg(),
            $this->getMachine(),
            $this->getEntity()
        );
    }
}
