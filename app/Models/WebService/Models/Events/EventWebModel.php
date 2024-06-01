<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Events;

use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\WebService\Models\WebModel;
use Nette\Schema\Expect;

/**
 * @phpstan-template TParams of array{eventId:int}
 * @phpstan-template TReturn of array
 * @phpstan-extends WebModel<TParams,TReturn>
 */
abstract class EventWebModel extends WebModel
{
    protected EventModel $event;
    protected EventService $eventService;

    public function inject(EventService $eventService): void
    {
        $this->eventService = $eventService;
    }

    protected function getExpectedParams(): array
    {
        return [
            'eventId' => Expect::scalar()->castTo('int')->required(),
        ];
    }

    /**
     * @throws NotFoundException
     */
    protected function getEvent(): EventModel
    {
        if (!isset($this->event)) {
            $event = $this->eventService->findByPrimary($this->params['eventId']);
            if (!$event) {
                throw new NotFoundException();
            }
            $this->event = $event;
        }
        return $this->event;
    }
}
