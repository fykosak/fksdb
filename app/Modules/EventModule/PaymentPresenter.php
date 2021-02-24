<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Entity\PaymentFormComponent;
use FKSDB\Components\Controls\Transitions\TransitionButtonsComponent;
use FKSDB\Components\Grids\Payment\EventPaymentGrid;
use FKSDB\Models\Entity\CannotAccessModelException;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\ORM\Services\ServicePayment;
use FKSDB\Models\Payment\PaymentExtension;
use FKSDB\Models\Payment\Transition\PaymentMachine;
use FKSDB\Models\Transitions\Machine;
use FKSDB\Models\UI\PageTitle;
use Nette\Application\AbortException;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\MissingServiceException;
use Nette\Security\IResource;

/**
 * Class PaymentPresenter
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
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    public function titleEdit(): void {
        $this->setPageTitle(new PageTitle(\sprintf(_('Edit payment #%s'), $this->getEntity()->getPaymentId()), 'fa fa-credit-card'));
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
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
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
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
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    public function renderEdit(): void {
        $this->template->model = $this->getEntity();
    }

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
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
     * @throws MissingServiceException
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
     * @return TransitionButtonsComponent
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    protected function createComponentTransitionButtons(): TransitionButtonsComponent {
        return new TransitionButtonsComponent($this->getMachine(), $this->getContext(), $this->getEntity());
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
     * @throws CannotAccessModelException
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
