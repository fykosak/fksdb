<?php

namespace FKSDB\Tests\PresentersTests\PageDisplay\EventModule;

use FKSDB\ORM\DbNames;

$container = require '../../../bootstrap.php';

/**
 * Class EventModule
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SingleEvent extends EventModuleTestCase {
    protected function getEventData(): array {
        return [
            'event_type_id' => 1,
            'year' => 1,
            'event_year' => 1,
            'begin' => new \DateTime(),
            'end' => new \DateTime(),
            'name' => 'TEST FOF',
        ];
    }

    public function getPages(): array {
        return [
            ['Event:Chart', 'list'],
            ['Event:Dashboard', 'default'],
            ['Event:Dispatch', 'default'],
            ['Event:EventOrg', 'list'],
            ['Event:EventOrg', 'create'],
            ['Event:Model', 'default'],
            ['Event:Seating', 'default'],
            ['Event:Seating', 'preview'],
            ['Event:Seating', 'list'],
            ['Event:TeamApplication', 'list'],
            ['Event:TeamApplication', 'transitions'],
        ];
    }
}

$testCase = new SingleEvent($container);
$testCase->run();
