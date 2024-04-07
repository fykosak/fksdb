<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Events;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\WebService\Models\WebModel;
use FKSDB\Modules\CoreModule\RestApiPresenter;
use Nette\Schema\Expect;

/**
 * @phpstan-import-type SerializedEventModel from EventModel
 * @phpstan-extends WebModel<array{eventTypes:array<int>},SerializedEventModel[]>
 */
class EventListWebModel extends WebModel
{
    private EventService $eventService;

    public function inject(EventService $eventService): void
    {
        $this->eventService = $eventService;
    }

    protected function getJsonResponse(): array
    {
        $query = $this->eventService->getTable()->where('event_type_id', $this->params['eventTypes']);
        $events = [];
        /** @var EventModel $event */
        foreach ($query as $event) {
            if ($this->eventAuthorizator->isAllowed(RestApiPresenter::RESOURCE_ID, self::class, $event)) {
                $events[$event->event_id] = $event->__toArray();
            }
        }
        return $events;
    }

    protected function getInnerStructure(): array
    {
        return [
            'eventTypes' => Expect::listOf(Expect::scalar()->castTo('int')),
        ];
    }

    protected function isAuthorized(): bool
    {
        return $this->contestAuthorizator->isAllowedAnyContest(RestApiPresenter::RESOURCE_ID, self::class);
    }
}
