<?php

namespace FKSDB\Tests\PresentersTests\PageDisplay\EventModule;

use FKSDB\ORM\DbNames;
use FKSDB\Tests\PresentersTests\PageDisplay\AbstractPageDisplayTestCase;

abstract class EventModuleTestCase extends AbstractPageDisplayTestCase {

    protected int $eventId;

    protected function setUp(): void {
        parent::setUp();
        $this->eventId = $this->insert(DbNames::TAB_EVENT, $this->getEventData());
    }

    abstract protected function getEventData(): array;

    protected function transformParams(string $presenterName, string $action, array $params): array {
        [$presenterName, $action, $params] = parent::transformParams($presenterName, $action, $params);
        $params['eventId'] = $this->eventId;
        return [$presenterName, $action, $params];
    }

    protected function tearDown(): void {
        $this->connection->query('DELETE FROM event');
        parent::tearDown();
    }
}
