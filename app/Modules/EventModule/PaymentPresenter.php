<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Transitions\TransitionButtonsComponent;
use FKSDB\Components\EntityForms\PaymentFormComponent;
use FKSDB\Components\Grids\Payment\EventPaymentGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Services\PaymentService;
use FKSDB\Models\Payment\PriceCalculator\PriceCalculator;
use FKSDB\Models\Transitions\Machine\PaymentMachine;
use Fykosak\Utils\Logging\Message;
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

    /* ********* titles *****************/
    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('New payment'), 'fa fa-credit-card');
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
            \sprintf(_('Edit payment #%s'), $this->getEntity()->getPaymentId()),
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
            \sprintf(_('Payment detail #%s'), $this->getEntity()->getPaymentId()),
            'fa fa-credit-card',
        );
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('List of payments'), 'fa fa-credit-card');
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    public function actionEdit(): void
    {
        if (!$this->isAllowed($this->getEntity(), 'edit')) {
            $this->flashMessage(
                \sprintf(_('Payment #%s can not be edited'), $this->getEntity()->getPaymentId()),
                Message::LVL_ERROR
            );
            $this->redirect(':Core:MyPayments:');
        }
    }
    /* ********* Authorization *****************/


    public function actionCreate(): void
    {
       /* if (\count($this->getMachine()->getAvailableTransitions($this->getMachine()->createHolder(null))) === 0) {
            $this->flashMessage(_('Payment is not allowed in this time!'));
            if (!$this->isOrg()) {
                $this->redirect(':Public:Dashboard:default');
            }
        }*/ //TODO
    }

    /* ********* actions *****************/

    /**
     * TODO!!!!
     * @throws EventNotFoundException
     * @throws GoneException
     */
    private function isOrg(): bool
    {
        return $this->isAllowed($this->getModelResource(), 'org');
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
        $this->template->items = $this->priceCalculator->getGridItems($payment);
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
        } catch (\Throwable $exception) {
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
        static $machine;
        if (!isset($machine)) {
            $machine = $this->getContext()->getService(
                sprintf('fyziklani%dpayment.machine', $this->getEvent()->event_year)
            );
            if (!$machine instanceof PaymentMachine) {
                throw new BadTypeException(PaymentMachine::class, $machine);
            }
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
    /* ********* Components *****************/
    /**
     * @throws BadTypeException
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
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws GoneException
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
     * @throws GoneException
     * @throws \ReflectionException
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
