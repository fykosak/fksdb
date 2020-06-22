<?php

namespace FKSDB\Tests\PageDisplay;

use FKSDB\ORM\DbNames;

$container = require '../../bootstrap.php';

/**
 * Class EventModule
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SingleEvent extends AbstractPageDisplayTestCase {

    const EVENT_ID = 1;

    protected function setUp() {
        parent::setUp();
        $this->connection->query("INSERT INTO event_type (event_type_id, contest_id, name) VALUES (1, 1, 'Fyziklání')");
        $this->insert(DbNames::TAB_EVENT, [
            'event_id' => self::EVENT_ID,
            'event_type_id' => 1,
            'year' => 1,
            'event_year' => 1,
            'begin' => new \DateTime(),
            'end' => new \DateTime(),
            'name' => 'TEST FOF',
        ]);
    }

    public function getPages(): array {
        return [
            ['Event:Chart', ['eventId' => self::EVENT_ID, 'action' => 'list']],
            ['Event:Dashboard', ['eventId' => self::EVENT_ID]],
            ['Event:Dispatch', []],
            ['Event:EventOrg', ['eventId' => self::EVENT_ID, 'action' => 'list']],
            ['Event:EventOrg', ['eventId' => self::EVENT_ID, 'action' => 'create']],
            ['Event:Model', ['eventId' => self::EVENT_ID]],

            // ['Event:Payment', ['eventId' => self::EVENT_ID, 'action' => 'list']],
            // ['Event:Payment', ['eventId' => self::EVENT_ID, 'action' => 'create']],

            ['Event:ScheduleGroup', ['eventId' => self::EVENT_ID, 'action' => 'list']],
            ['Event:ScheduleGroup', ['eventId' => self::EVENT_ID, 'action' => 'persons']],

            // ['Event:ScheduleItem', ['eventId' => self::EVENT_ID, 'action' => 'list']],

            ['Event:Seating', ['eventId' => self::EVENT_ID, 'action' => 'default']],
            ['Event:Seating', ['eventId' => self::EVENT_ID, 'action' => 'preview']],
            ['Event:Seating', ['eventId' => self::EVENT_ID, 'action' => 'list']],

            ['Event:TeamApplication', ['eventId' => self::EVENT_ID, 'action' => 'list']],
            ['Event:TeamApplication', ['eventId' => self::EVENT_ID, 'action' => 'transitions']],
        ];
    }

    protected function tearDown() {
        $this->connection->query('DELETE FROM event');
        $this->connection->query('DELETE FROM event_type');
        parent::tearDown();
    }
}

$testCase = new SingleEvent($container);
$testCase->run();
