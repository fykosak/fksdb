<?php

declare(strict_types=1);

namespace FKSDB\Modules\ShopModule;

use FKSDB\Components\EntityForms\PaymentForm;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;

final class EventsPresenter extends BasePresenter
{
    private const AvailableEventIds = []; //phpcs:ignore

    private EventService $eventService;

    /**
     * @throws NotFoundException
     * @throws NotImplementedException
     */
    private function getEvent(): EventModel
    {
        $eventId = $this->getParameter('eventId');
        if (!in_array($eventId, self::AvailableEventIds)) {
            throw new NotImplementedException();
        }
        $event = $this->eventService->findByPrimary($eventId);
        if (!$event) {
            throw new NotFoundException();
        }
        return $event;
    }

    /**
     * @throws NotFoundException
     * @throws NotImplementedException
     */
    protected function createComponentCreateForm(): PaymentForm
    {
        return new PaymentForm(
            $this->getContext(),
            [$this->getEvent()],
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
            [$this->getEvent()],
            $this->getLoggedPerson(),
            false,
            $this->getMachine(),
            $this->getInProgressPayment()
        );
    }
}
