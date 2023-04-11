<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PageDisplay\EventModule;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Tests\PresentersTests\PageDisplay\AbstractPageDisplayTestCase;

abstract class EventModuleTestCase extends AbstractPageDisplayTestCase
{

    protected EventModel $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->event = $this->container->getByType(EventService::class)->storeModel($this->getEventData());
    }

    abstract protected function getEventData(): array;

    protected function transformParams(string $presenterName, string $action, array $params): array
    {
        [$presenterName, $action, $params] = parent::transformParams($presenterName, $action, $params);
        $params['eventId'] = $this->event->event_id;
        return [$presenterName, $action, $params];
    }
}
