<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Transitions\TransitionButtonsComponent;
use FKSDB\Components\EntityForms\PaymentFormComponent;
use FKSDB\Components\Grids\Payment\EventPaymentGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\ORM\Services\ServicePayment;
use FKSDB\Models\Payment\Transition\PaymentMachine;
use FKSDB\Models\Transitions\Machine;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\MissingServiceException;
use Nette\Security\Resource;

/**
 * @method ModelPayment getEntity
 */
class PaymentPresenter extends BasePresenter
{
    use EventEntityPresenterTrait;

    private Machine\Machine $machine;
    private ServicePayment $servicePayment;

    final public function injectServicePayment(ServicePayment $servicePayment): void
    {
        $this->servicePayment = $servicePayment;
    }

    /* ********* titles *****************/
    public function titleCreate(): PageTitle
    {
        return new PageTitle(_('New payment'), 'fa fa-credit-card');
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(\sprintf(_('Edit payment #%s'), $this->getEntity()->getPaymentId()), 'fa fa-credit-card');
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(
            \sprintf(_('Payment detail #%s'), $this->getEntity()->getPaymentId()),
            'fa fa-credit-card',
        );
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(_('List of payments'), 'fa fa-credit-card');
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    public function actionEdit(): void
    {
        if (!$this->isContestsOrgAuthorized($this->getEntity(), 'edit')) {
            $this->flashMessage(
                \sprintf(_('Payment #%s can not be edited'), $this->getEntity()->getPaymentId()),
                \FKSDB\Modules\Core\BasePresenter::FLASH_ERROR
            );
            $this->redirect(':Core:MyPayments:');
        }
    }
    /* ********* Authorization *****************/

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     */
    public function actionCreate(): void
    {
        if (\count($this->getMachine()->getAvailableTransitions($this->getMachine()->createHolder(null))) === 0) {
            $this->flashMessage(_('Payment is not allowed in this time!'));
            if (!$this->isOrg()) {
                $this->redirect(':Core:Dashboard:default');
            }
        }
    }

    /* ********* actions *****************/

    /**
     * TODO!!!!
     * @throws EventNotFoundException
     */
    private function isOrg(): bool
    {
        return $this->isContestsOrgAuthorized($this->getModelResource(), 'org');
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    final public function renderEdit(): void
    {
        $this->template->model = $this->getEntity();
    }

    /* ********* render *****************/

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    final public function renderDetail(): void
    {
        $payment = $this->getEntity();
        $this->template->items = $this->getMachine()->getPriceCalculator()->getGridItems($payment);
        $this->template->model = $payment;
        $this->template->isOrg = $this->isOrg();
    }

    protected function isEnabled(): bool
    {
        return $this->hasApi();
    }

    private function hasApi(): bool
    {
        try {
            $this->getMachine();
        } catch (\Exception $exception) {
            return false;
        }
        return true;
    }

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws MissingServiceException
     */
    private function getMachine(): PaymentMachine
    {
        if (!isset($this->machine)) {
            $machine = $this->getContext()->getService(
                sprintf('fyziklani%dpayment.machine', $this->getEvent()->event_year)
            );
            if (!$machine instanceof PaymentMachine) {
                throw new BadTypeException(PaymentMachine::class, $this->machine);
            }
            $this->machine = $machine;
        }
        return $this->machine;
    }

    /**
     * @param Resource|string|null $resource
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isContestsOrgAuthorized($resource, $privilege);
    }

    protected function getORMService(): ServicePayment
    {
        return $this->servicePayment;
    }
    /* ********* Components *****************/
    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    protected function createComponentTransitionButtons(): TransitionButtonsComponent
    {
        return new TransitionButtonsComponent(
            $this->getMachine(),
            $this->getContext(),
            $this->getMachine()->createHolder($this->getEntity())
        );
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): EventPaymentGrid
    {
        return new EventPaymentGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): PaymentFormComponent
    {
        return new PaymentFormComponent(
            $this->getContext(),
            $this->isOrg(),
            $this->getMachine(),
            null
        );
    }

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    protected function createComponentEditForm(): PaymentFormComponent
    {
        return new PaymentFormComponent(
            $this->getContext(),
            $this->isOrg(),
            $this->getMachine(),
            $this->getEntity()
        );
    }
}
