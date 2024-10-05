<?php

declare(strict_types=1);

namespace FKSDB\Modules\ShopModule;

use FKSDB\Components\Controls\Transition\TransitionButtonsComponent;
use FKSDB\Components\Payments\PaymentQRCode;
use FKSDB\Components\Payments\SchedulePaymentForm;
use FKSDB\Models\Authorization\Resource\EventResourceHolder;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\ORM\Services\PaymentService;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\UI\PageTitle;

final class EventsPresenter extends BasePresenter
{
    private EventService $eventService;
    private PaymentService $paymentService;

    /** @persistent */
    public int $eventId;
    /** @persistent */
    public ?int $id;

    public function injectServices(EventService $eventService, PaymentService $paymentService): void
    {
        $this->eventService = $eventService;
        $this->paymentService = $paymentService;
    }

    /**
     * @throws NotFoundException
     */
    public function authorizedCreate(): bool
    {
        return $this->authorizator->isAllowedEvent(
            EventResourceHolder::fromResourceId(PaymentModel::RESOURCE_ID, $this->getEvent()),
            'create',
            $this->getEvent()
        );
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create an event payment'), 'fas fa-credit-card');
    }

    /**
     * @throws NotFoundException
     */
    public function renderCreate(): void
    {
        $this->template->roles = $this->getLoggedPerson()->getEventRoles($this->getEvent());
    }

    /**
     * @throws NotFoundException
     */
    public function authorizedDetail(): bool
    {
        return $this->authorizator->isAllowedEvent(
            EventResourceHolder::fromOwnResource($this->getPayment()),
            'detail',
            $this->getEvent()
        );
    }

    /**
     * @throws CannotAccessModelException
     * @throws NotFoundException
     * @throws NotImplementedException
     */
    final public function renderDetail(): void
    {
        $this->template->model = $this->getPayment();
    }

    /**
     * @throws CannotAccessModelException
     * @throws NotFoundException
     * @throws NotImplementedException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(
            null,
            \sprintf(_('Detail of the payment %s'), $this->getPayment()->payment_id),
            'fas fa-credit-card'
        );
    }


    /**
     * @throws NotFoundException
     */
    public function authorizedEdit(): bool
    {
        return $this->authorizator->isAllowedEvent(
            EventResourceHolder::fromOwnResource($this->getPayment()),
            'edit',
            $this->getEvent()
        );
    }

    /**
     * @throws CannotAccessModelException
     * @throws NotFoundException
     */
    final public function renderEdit(): void
    {
        $this->template->model = $this->getPayment();
        $this->template->roles = $this->getLoggedPerson()->getEventRoles($this->getEvent());
    }

    /**
     * @throws CannotAccessModelException
     * @throws NotFoundException
     * @throws NotImplementedException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            null,
            \sprintf(_('Edit payment #%s'), $this->getPayment()->payment_id),
            'fas fa-credit-card'
        );
    }

    /**
     * @throws NotFoundException
     */
    private function getEvent(): EventModel
    {
        $event = $this->eventService->findByPrimary($this->eventId);
        if (!$event) {
            throw new NotFoundException();
        }
        return $event;
    }

    /**
     * @throws NotFoundException
     * @throws NotImplementedException
     */
    public function getPayment(): PaymentModel
    {
        $payment = $this->paymentService->findByPrimary($this->id);
        if (!$payment) {
            throw new NotFoundException();
        }
        if (!$payment->getRelatedEvent() || $payment->getRelatedEvent()->event_id !== $this->getEvent()->event_id) {
            throw new NotFoundException();
        }
        return $payment;
    }

    /**
     * @throws NotFoundException
     * @throws NotImplementedException
     */
    protected function createComponentPaymentQRCode(): PaymentQRCode
    {
        return new PaymentQRCode($this->getContext(), $this->getPayment());
    }

    /**
     * @throws NotFoundException
     */
    protected function createComponentCreateForm(): SchedulePaymentForm
    {
        return new SchedulePaymentForm(
            $this->getContext(),
            $this->getEvent(),
            $this->getLoggedPerson(),
            false,
            $this->getMachine(),
            null
        );
    }

    /**
     * @throws NotFoundException
     */
    protected function createComponentEditForm(): SchedulePaymentForm
    {
        return new SchedulePaymentForm(
            $this->getContext(),
            $this->getEvent(),
            $this->getLoggedPerson(),
            false,
            $this->getMachine(),
            $this->getPayment()
        );
    }

    /**
     * @throws NotFoundException
     * @throws NotImplementedException
     * @phpstan-return TransitionButtonsComponent<PaymentModel>
     */
    protected function createComponentButtonTransition(): TransitionButtonsComponent
    {
        return new TransitionButtonsComponent(
            $this->getContext(),
            $this->getMachine(), // @phpstan-ignore-line
            $this->getPayment()
        );
    }
}
