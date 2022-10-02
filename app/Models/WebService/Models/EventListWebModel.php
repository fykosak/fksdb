<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

class EventListWebModel extends WebModel
{

    private EventService $eventService;

    public function inject(EventService $eventService): void
    {
        $this->eventService = $eventService;
    }

    /**
     * @throws GoneException
     */
    public function getResponse(\stdClass $args): \SoapVar
    {
        throw new GoneException();
    }

    public function getJsonResponse(array $params): array
    {
        $query = $this->eventService->getTable()->where('event_type_id', $params['event_type_ids']);
        $events = [];
        /** @var EventModel $event */
        foreach ($query as $event) {
            $events[$event->event_id] = $event->__toArray();
        }
        return $events;
    }

    public function getExpectedParams(): Structure
    {
        return Expect::structure([
            'event_type_ids' => Expect::listOf(Expect::scalar()->castTo('int'))->required(),
        ]);
    }
}
