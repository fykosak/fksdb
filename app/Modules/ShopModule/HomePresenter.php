<?php

declare(strict_types=1);

namespace FKSDB\Modules\ShopModule;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;
use Fykosak\Utils\UI\PageTitle;
use Tracy\Debugger;

final class HomePresenter extends BasePresenter
{
    private EventService $eventService;

    public function injectEventService(EventService $eventService): void
    {
        $this->eventService = $eventService;
    }

    public function authorizedDefault(): bool
    {
        return true;
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Shop & payments'));
    }

    public function renderDefault(): void
    {
        $rests = [];
        $events = [];
        $persons = [];
        /** @var EventModel $event */
        foreach ($this->eventService->getTable() as $event) {
            $events[$event->event_id] = $event;
            $relatedPersons = $this->getLoggedPerson()->getEventRelatedPersons($event);
            foreach ($relatedPersons as $person) {
                $restArray = $person->getScheduleRestsForEvent($event);
                if (count($restArray)) {
                    $persons[$person->person_id] = $person;
                    $rests[$event->event_id][$person->person_id] = $restArray;
                }
            }
        }

        $this->template->payments = $this->getLoggedPerson()->getPayments();
        $this->template->rests = $rests;
        $this->template->events = $events;
        $this->template->persons = $persons;
    }
}
