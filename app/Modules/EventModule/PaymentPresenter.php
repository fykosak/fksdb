<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Transition\TransitionButtonsComponent;
use FKSDB\Components\EntityForms\PaymentFormComponent;
use FKSDB\Components\Grids\Payment\EventPaymentGrid;
use FKSDB\Components\Grids\Payment\PaymentList;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Services\PaymentService;
use FKSDB\Models\Payment\PriceCalculator\PriceCalculator;
use FKSDB\Models\Transitions\Machine\PaymentMachine;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Resource;
use Tracy\Debugger;

/**
 * @method PaymentModel getEntity
 */
class PaymentPresenter extends BasePresenter
{
    use EventEntityPresenterTrait;

    private PaymentService $paymentService;
    private PriceCalculator $priceCalculator;

    final public function injectServicePayment(
        PaymentService $paymentService,
        PriceCalculator $priceCalculator
    ): void {
        $this->paymentService = $paymentService;
        $this->priceCalculator = $priceCalculator;
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create payment'), 'fas fa-credit-card');
    }

    public function authorizedCreate(): bool
    {
        $event = $this->getEvent();
        return $this->eventAuthorizator->isAllowed(PaymentModel::RESOURCE_ID, 'org-create', $event)
            || ($this->isPaymentAllowed() &&
                $this->eventAuthorizator->isAllowed(PaymentModel::RESOURCE_ID, 'create', $event));
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            null,
            \sprintf(_('Edit payment #%s'), $this->getEntity()->payment_id),
            'fas fa-credit-card'
        );
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws \ReflectionException
     */
    public function authorizedEdit(): bool
    {
        $event = $this->getEvent();
        return $this->eventAuthorizator->isAllowed($this->getEntity(), 'org-edit', $event)
            || ($this->isPaymentAllowed() && $this->eventAuthorizator->isAllowed($this->getEntity(), 'edit', $event));
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(
            null,
            \sprintf(_('Detail of the payment #%s'), $this->getEntity()->payment_id),
            'fas fa-credit-card',
        );
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('List of payments'), 'fas fa-credit-card');
    }

    public function titleDetailedList(): PageTitle
    {
        return new PageTitle(null, _('Detailed list of payments'), 'fas fa-credit-card');
    }

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     */
    public function authorizedDetailedList(): bool
    {
        return $this->authorizedList();
    }

    /**
     * @throws EventNotFoundException
     */
    private function isPaymentAllowed(): bool
    {
        $params = $this->getContext()->parameters[$this->eventDispatchFactory->getPaymentFactoryName(
            $this->getEvent()
        )];
        if (!isset($params['begin']) || !isset($params['end'])) {
            return false;
        }
        return (time() > $params['begin']->getTimestamp()) && (time() < $params['end']->getTimestamp());
    }
    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    final public function renderEdit(): void
    {
        $this->template->model = $this->getEntity();
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    final public function renderDetail(): void
    {
        $payment = $this->getEntity();
        $this->template->model = $payment;
    }

    protected function isEnabled(): bool
    {
        try {
            $this->getMachine();
        } catch (\Throwable $exception) {
            return false;
        }
        return true;
    }

    /**
     * @throws EventNotFoundException
     * @throws  BadTypeException
     */
    private function getMachine(): PaymentMachine
    {
        static $machine;
        if (!isset($machine)) {
            $machine = $this->eventDispatchFactory->getPaymentMachine($this->getEvent());
        }
        return $machine;
    }

    /**
     * @param Resource|string|null $resource
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isAllowed($resource, $privilege);
    }

    protected function getORMService(): PaymentService
    {
        return $this->paymentService;
    }

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws \ReflectionException
     */
    protected function createComponentTransitionButtons(): TransitionButtonsComponent
    {
        return new TransitionButtonsComponent(
            $this->getContext(),
            $this->getEvent(),
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
     * @throws EventNotFoundException
     */
    protected function createComponentList(): PaymentList
    {
        return new PaymentList($this->getContext(), $this->getEvent());
    }

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): PaymentFormComponent
    {
        return new PaymentFormComponent(
            $this->getContext(),
            $this->getEvent(),
            $this->isAllowed(PaymentModel::RESOURCE_ID, 'org-create'),
            $this->getMachine(),
            null
        );
    }

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws \ReflectionException
     */
    protected function createComponentEditForm(): PaymentFormComponent
    {
        return new PaymentFormComponent(
            $this->getContext(),
            $this->getEvent(),
            $this->isAllowed($this->getEntity(), 'org-edit'),
            $this->getMachine(),
            $this->getEntity()
        );
    }
}
