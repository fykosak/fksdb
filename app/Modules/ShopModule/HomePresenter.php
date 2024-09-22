<?php

declare(strict_types=1);

namespace FKSDB\Modules\ShopModule;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
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
        foreach ($this->eventService->getTable()->where('event_id', self::AvailableEventIds) as $event) {
            $events[$event->event_id] = $event;
            $relatedPersons = $this->getLoggedPerson()->getEventRelatedPersons($event);
            foreach ($relatedPersons as $person) {
                $persons[$person->person_id] = $person;
                $rests[$event->event_id][$person->person_id] = $person->getScheduleRestsForEvent($event);
            }
        }

        $this->template->payments = $this->getLoggedPerson()->getPayments();
        Debugger::barDump($rests);
        Debugger::barDump($events);
        Debugger::barDump($persons);
        $this->template->rests = $rests;
        $this->template->events = $events;
        $this->template->persons = $persons;
    }
}
