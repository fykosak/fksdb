<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PageDisplay\EventModule;

// phpcs:disable
$container = require '../../../Bootstrap.php';

// phpcs:enable

use DateTime;

class TeamEvent extends EventModuleTestCase
{
    protected function getEventData(): array
    {
        return [
            'event_type_id' => 1,
            'year' => 1,
            'event_year' => 1,
            'begin' => new DateTime(),
            'end' => new DateTime(),
            'name' => 'TEST FOF',
        ];
    }

    public function getPages(): array
    {
        return [
            ['Event:Chart', 'list'],
            // ['Event:Chart', 'participantAcquaintance'],
            // ['Event:Chart', 'singleApplicationProgress'],
            ['Event:Chart', 'teamApplicationProgress'],
            ['Event:Chart', 'model'],
            ['Event:Dashboard', 'default'],
            ['Event:Dispatch', 'default'],
            ['Event:EventOrg', 'list'],
            ['Event:EventOrg', 'create'],
            ['Event:TeamApplication', 'list'],
            ['Event:TeamApplication', 'detailedList'],
            ['Event:TeamApplication', 'detailedList'],
            /* ['Event:TeamApplication', 'transitions'],*/
        ];
    }
}

// phpcs:disable
$testCase = new TeamEvent($container);
$testCase->run();
// phpcs:enable
