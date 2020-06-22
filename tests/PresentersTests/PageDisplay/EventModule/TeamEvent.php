<?php

namespace FKSDB\Tests\PresentersTests\PageDisplay\EventModule;

$container = require '../../../bootstrap.php';

/**
 * Class EventModule
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TeamEvent extends EventModuleTestCase {
    protected function getEventData(): array {
        return [
            'event_type_id' => 7,
            'year' => 1,
            'event_year' => 1,
            'begin' => new \DateTime(),
            'end' => new \DateTime(),
            'name' => 'TEST TSAF',
        ];
    }

    public function getPages(): array {
        return [
            ['Event:Application', 'list'],
            ['Event:Application', 'import'],
            ['Event:Application', 'transitions'],
            ['Event:Chart', 'list'],
            ['Event:Dashboard', 'default'],
            ['Event:Dispatch', 'default'],
            ['Event:EventOrg', 'list'],
            ['Event:EventOrg', 'create'],
            ['Event:Model', 'default'],
            ['Event:Seating', 'default'],
            ['Event:Seating', 'preview'],
            ['Event:Seating', 'list'],
        ];
    }
}

$testCase = new TeamEvent($container);
$testCase->run();
