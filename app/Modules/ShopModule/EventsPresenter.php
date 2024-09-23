<?php

declare(strict_types=1);

namespace FKSDB\Modules\ShopModule;

use FKSDB\Components\Controls\Transition\TransitionButtonsComponent;
use FKSDB\Components\EntityForms\Payments\PaymentForm;
use FKSDB\Components\Payments\PaymentQRCode;
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
     * @throws NotImplementedException
     */
    public function authorizedCreate(): bool
    {
        return $this->eventAuthorizator->isAllowed(PaymentModel::RESOURCE_ID, 'create', $this->getEvent());
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create payment for events'), 'fas fa-credit-card');
    }

    /**
     * @throws NotFoundException
     * @throws NotImplementedException
     */
    public function authorizedDetail(): bool
    {
        return $this->eventAuthorizator->isAllowed($this->getPayment(), 'detail', $this->getEvent());
    }

    /**
     * @throws CannotAccessModelException
     * @throws NotFoundException
     */
    final public function renderDetail(): void
    {
        $this->template->model = $this->getPayment();
    }

    /**
     * @throws CannotAccessModelException
     * @throws NotFoundException
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
     * @throws NotImplementedException
     */
    public function authorizedEdit(): bool
    {
        return $this->eventAuthorizator->isAllowed($this->getPayment(), 'edit', $this->getEvent());
    }

    /**
     * @throws CannotAccessModelException
     * @throws NotFoundException
     */
    final public function renderEdit(): void
    {
        $this->template->model = $this->getPayment();
    }

    /**
     * @throws CannotAccessModelException
     * @throws NotFoundException
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
     * @throws NotImplementedException
     */
    private function getEvent(): EventModel
    {
        if (!in_array($this->eventId, self::AvailableEventIds)) {
            throw new NotImplementedException();
        }
        $event = $this->eventService->findByPrimary($this->eventId);
        if (!$event) {
            throw new NotFoundException();
        }
        return $event;
    }

    /**
     * @throws NotFoundException
     */
    public function getPayment(): PaymentModel
    {
        $payment = $this->paymentService->findByPrimary($this->id);
        if (!$payment) {
            throw new NotFoundException();
        }
        return $payment;
    }

    /**
     * @throws NotFoundException
     */
    protected function createComponentPaymentQRCode(): PaymentQRCode
    {
        return new PaymentQRCode($this->getContext(), $this->getPayment());
    }

    /**
     * @throws NotImplementedException
     * @throws NotFoundException
     */
    protected function createComponentCreateForm(): PaymentForm
    {
        return new PaymentForm(
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
     * @throws NotImplementedException
     */
    protected function createComponentEditForm(): PaymentForm
    {
        return new PaymentForm(
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
