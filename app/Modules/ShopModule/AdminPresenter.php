<?php

declare(strict_types=1);

namespace FKSDB\Modules\ShopModule;

use FKSDB\Components\Controls\Transition\TransitionButtonsComponent;
use FKSDB\Components\Payments\AdminPaymentList;
use FKSDB\Components\Payments\PaymentQRCode;
use FKSDB\Components\Payments\SchedulePaymentForm;
use FKSDB\Components\Schedule\Rests\AllRestsComponent;
use FKSDB\Models\Authorization\Resource\ContestResourceHolder;
use FKSDB\Models\Authorization\Resource\EventResourceHolder;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\ORM\Services\PaymentService;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\UI\PageTitle;

final class AdminPresenter extends BasePresenter
{
    private PaymentService $paymentService;
    private EventService $eventService;

    /** @persistent */
    public ?int $id;

    public function injectServices(
        PaymentService $paymentService,
        EventService $eventService
    ): void {
        $this->paymentService = $paymentService;
        $this->eventService = $eventService;
    }

    /**
     * @throws NotFoundException
     */
    public function authorizedCreate(): bool
    {
        return $this->authorizator->isAllowedEvent(
            EventResourceHolder::fromResourceId(PaymentModel::RESOURCE_ID, $this->getEvent()),
            'organizer',
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
    public function authorizedDetail(): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromResource($this->getPayment(), $this->getContest()),
            'organizer',
            $this->getContest()
        );
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
     */
    public function authorizedEdit(): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromResource($this->getPayment(), $this->getContest()),
            'organizer',
            $this->getContest()
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
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            null,
            \sprintf(_('Edit payment #%s'), $this->getPayment()->payment_id),
            'fas fa-credit-card'
        );
    }

    public function authorizedDefault(): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromResourceId(PaymentModel::RESOURCE_ID, $this->getContest()),
            'organizer',
            $this->getContest()
        );
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Payment dashboard'), 'fas fa-dashboard');
    }

    public function authorizedSchedule(): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromResourceId(PaymentModel::RESOURCE_ID, $this->getContest()),
            'organizer',
            $this->getContest()
        );
    }

    public function titleSchedule(): PageTitle
    {
        return new PageTitle(null, _('Schedule payment dashboard'), 'fas fa-dashboard');
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
    private function getEvent(): EventModel
    {
        $eventId = $this->getParameter('eventId');
        $event = $this->eventService->findByPrimary($eventId);
        if (!$event) {
            throw new NotFoundException();
        }
        return $event;
    }

    protected function createComponentGrid(): AdminPaymentList
    {
        return new AdminPaymentList($this->getContext());
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

    /**
     * @throws NotFoundException
     */
    protected function createComponentPaymentQRCode(): PaymentQRCode
    {
        return new PaymentQRCode($this->getContext(), $this->getPayment());
    }

    protected function createComponentAllRests(): AllRestsComponent
    {
        return new AllRestsComponent($this->getContext());
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
            true,
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
            true,
            $this->getMachine(),
            $this->getPayment()
        );
    }
}
