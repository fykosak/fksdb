<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Transition\TransitionButtonsComponent;
use FKSDB\Components\EntityForms\PaymentForm;
use FKSDB\Components\Payments\PaymentList;
use FKSDB\Components\Payments\PaymentQRCode;
use FKSDB\Models\Authorization\Resource\EventResource;
use FKSDB\Models\Authorization\Resource\FakeEventResource;
use FKSDB\Models\Authorization\Resource\PseudoEventResource;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Services\PaymentService;
use FKSDB\Models\Transitions\Machine\PaymentMachine;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\UI\PageTitle;
use Nette\Utils\Html;

final class PaymentsPresenter extends BasePresenter
{
    /** @phpstan-use EntityPresenterTrait<PaymentModel> */
    use EntityPresenterTrait;

    private PaymentService $paymentService;

    public function inject(PaymentService $paymentService): void
    {
        $this->paymentService = $paymentService;
    }

    public function authorizedCreate(): bool
    {
        $event = $this->getEvent();
        return $this->eventAuthorizator->isAllowed(
                new PseudoEventResource(PaymentModel::RESOURCE_ID, $event),
                'organizer',
                $event
            )
            || ($this->isPaymentAllowed() &&
                $this->eventAuthorizator->isAllowed(
                    new PseudoEventResource(PaymentModel::RESOURCE_ID, $event),
                    'create',
                    $event
                ));
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create payment'), 'fas fa-credit-card');
    }

    /**
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws NotFoundException
     */
    final public function renderDetail(): void
    {
        $payment = $this->getEntity();
        $this->template->model = $payment;
    }

    /**
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws NotFoundException
     * @throws NotFoundException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(
            null,
            Html::el('')
                ->addText(\sprintf(_('Detail of the payment %s'), $this->getEntity()->payment_id))
                ->addHtml(
                    Html::el('small')->addAttributes(['class' => 'ms-2'])->addHtml(
                        $this->getEntity()->state->badge()
                    )
                ),
            'fas fa-credit-card',
        );
    }


    /**
     * @throws EventNotFoundException
     * @throws GoneException
     * @throws NotFoundException
     * @throws NotFoundException
     */
    public function authorizedEdit(): bool
    {
        $event = $this->getEvent();
        return $this->eventAuthorizator->isAllowed(
                new FakeEventResource($this->getEntity(), $this->getEvent()),
                'organizer',
                $event
            )
            || (
                $this->isPaymentAllowed()
                && $this->eventAuthorizator->isAllowed(
                    new FakeEventResource($this->getEntity(), $this->getEvent()),
                    'edit',
                    $event
                )
            ); // TODO
    }

    /**
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws NotFoundException
     */
    final public function renderEdit(): void
    {
        $this->template->model = $this->getEntity();
    }

    /**
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws NotFoundException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            null,
            \sprintf(_('Edit payment #%s'), $this->getEntity()->payment_id),
            'fas fa-credit-card'
        );
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('List of payments'), 'fas fa-credit-card');
    }

    /**
     * @throws EventNotFoundException
     */
    private function isPaymentAllowed(): bool
    {
        return time() < $this->getEvent()->begin->getTimestamp();
    }

    /**
     * @throws EventNotFoundException
     */
    protected function isEnabled(): bool
    {
        try {
            $this->getMachine();
        } catch (\Throwable $exception) {
            return false;
        }
        return $this->getEvent()->event_id === 180;
    }

    private function getMachine(): PaymentMachine
    {
        static $machine;
        if (!isset($machine)) {
            $machine = $this->eventDispatchFactory->getPaymentMachine();
        }
        return $machine;
    }

    /**
     * @param EventResource $resource
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->eventAuthorizator->isAllowed($resource, $privilege, $this->getEvent());
    }

    protected function getORMService(): PaymentService
    {
        return $this->paymentService;
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     * @phpstan-return TransitionButtonsComponent<PaymentModel>
     */
    protected function createComponentButtonTransition(): TransitionButtonsComponent
    {
        return new TransitionButtonsComponent(
            $this->getContext(),
            $this->getMachine(), // @phpstan-ignore-line
            $this->getEntity()
        );
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): PaymentList
    {
        return new PaymentList($this->getContext(), $this->getEvent());
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): PaymentForm
    {
        return new PaymentForm(
            $this->getContext(),
            [$this->getEvent()],
            $this->getLoggedPerson(),
            $this->eventAuthorizator->isAllowed(
                new PseudoEventResource(PaymentModel::RESOURCE_ID, $this->getEvent()),
                'organizer',
                $this->getEvent()
            ),
            $this->getMachine(),
            null
        );
    }

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     * @throws NotFoundException
     * @throws NotFoundException
     */
    protected function createComponentEditForm(): PaymentForm
    {
        return new PaymentForm(
            $this->getContext(),
            [$this->getEvent()],
            $this->getLoggedPerson(),
            $this->eventAuthorizator->isAllowed(
                new FakeEventResource($this->getEntity(), $this->getEvent()),
                'organizer',
                $this->getEvent()
            ),
            $this->getMachine(),
            $this->getEntity()
        );
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    protected function createComponentPaymentQRCode(): PaymentQRCode
    {
        return new PaymentQRCode($this->getContext(), $this->getEntity());
    }
}
