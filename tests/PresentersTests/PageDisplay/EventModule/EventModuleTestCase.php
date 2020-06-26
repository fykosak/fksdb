<?php

namespace FKSDB\Tests\PresentersTests\PageDisplay\EventModule;

use FKSDB\ORM\DbNames;
use FKSDB\Tests\PresentersTests\PageDisplay\AbstractPageDisplayTestCase;

abstract class EventModuleTestCase extends AbstractPageDisplayTestCase {
    /** @var int */
    protected $eventId;

    protected function setUp() {
        parent::setUp();
        $this->eventId = $this->insert(DbNames::TAB_EVENT, [
            'event_type_id' => 1,
            'year' => 1,
            'event_year' => 1,
            'begin' => new \DateTime(),
            'end' => new \DateTime(),
            'name' => 'TEST FOF',
        ]);
    }

    abstract protected function getEventData(): array;

    protected function transformParams(string $presenterName, string $action, array $params): array {
        list($presenterName, $action, $params) = parent::transformParams($presenterName, $action, $params);
        $params['eventId'] = $this->eventId;
        return [$presenterName, $action, $params];
    }

    protected function tearDown() {
        $this->connection->query('DELETE FROM event');
        parent::tearDown();
    }
}
