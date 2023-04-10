<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Transitions\TransitionButtonsComponent;
use FKSDB\Components\EntityForms\PaymentFormComponent;
use FKSDB\Components\Grids\Payment\EventPaymentGrid;
use FKSDB\Components\Grids\Payment\PaymentList;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Services\PaymentService;
use FKSDB\Models\Payment\PriceCalculator\PriceCalculator;
use FKSDB\Models\Transitions\Machine\PaymentMachine;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\MissingServiceException;
use Nette\Security\Resource;

/**
 * @method PaymentModel getEntity
 */
class PaymentPresenter extends BasePresenter
{
    use EventEntityPresenterTrait;

    private PaymentService $paymentService;
    private PriceCalculator $priceCalculator;

    final public function injectServicePayment(PaymentService $paymentService, PriceCalculator $priceCalculator): void
    {
        $this->paymentService = $paymentService;
        $this->priceCalculator = $priceCalculator;
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create payment'), 'fa fa-credit-card');
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
            'fa fa-credit-card'
        );
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
            'fa fa-credit-card',
        );
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('List of payments'), 'fa fa-credit-card');
    }

    public function titleDetailedList(): PageTitle
    {
        return new PageTitle(null, _('Detailed list of payments'), 'fa fa-credit-card');
    }

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     */
    public function authorizedDetailedList(): void
    {
        $this->authorizedList();
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws \ReflectionException
     */
    public function authorizedEdit(): void
    {
        $event = $this->getEvent();
        $this->setAuthorized(
            $this->eventAuthorizator->isAllowed($this->getEntity(), 'org-edit', $event)
            || ($this->isPaymentAllowed()
                && $this->eventAuthorizator->isAllowed($this->getEntity(), 'edit', $event)
            )
        );
    }

    public function authorizedCreate(): void
    {
        $event = $this->getEvent();
        $this->setAuthorized(
            $this->eventAuthorizator->isAllowed(PaymentModel::RESOURCE_ID, 'org-create', $event)
            || ($this->isPaymentAllowed()
                && $this->eventAuthorizator->isAllowed(PaymentModel::RESOURCE_ID, 'create', $event)
            )
        );
    }

    /* ********* Authorization *****************/
    /**
     * @throws EventNotFoundException
     */
    private function isPaymentAllowed(): bool
    {
        $params = $this->getContext()->parameters[$this->getEvent()->getPaymentFactoryName()];
        if (!isset($params['begin']) || !isset($params['end'])) {
            return false;
        }
        return (time() > $params['begin']->getTimestamp()) && (time() < $params['end']->getTimestamp());
    }

    /* ********* actions *****************/

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

    /* ********* render *****************/

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
     * @throws MissingServiceException
     */
    private function getMachine(): PaymentMachine
    {
        static $machine;
        if (!isset($machine)) {
            $machine = $this->getContext()->getService($this->getEvent()->getPaymentFactoryName() . '.machine');
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
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
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
     * @throws EventNotFoundException
     */
    protected function createComponentList(): PaymentList
    {
        return new PaymentList($this->getContext(), $this->getEvent());
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): PaymentFormComponent
    {
        return new PaymentFormComponent(
            $this->getContext(),
            $this->isAllowed(PaymentModel::RESOURCE_ID, 'org-create'),
            $this->getMachine(),
            null
        );
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    protected function createComponentEditForm(): PaymentFormComponent
    {
        return new PaymentFormComponent(
            $this->getContext(),
            $this->isAllowed($this->getEntity(), 'org-edit'),
            $this->getMachine(),
            $this->getEntity()
        );
    }
}
