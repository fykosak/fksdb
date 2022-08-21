<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PageDisplay\EventModule;

use DateTime;

$container = require '../../../Bootstrap.php';

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
            ['Event:Chart', 'participantAcquaintance'],
            ['Event:Chart', 'singleApplicationProgress'],
            ['Event:Chart', 'teamApplicationProgress'],
            ['Event:Chart', 'model'],
            ['Event:Dashboard', 'default'],
            ['Event:Dispatch', 'default'],
            ['Event:EventOrg', 'list'],
            ['Event:EventOrg', 'create'],
            ['Event:TeamApplication', 'list'],
            ['Event:TeamApplication', 'transitions'],
        ];
    }
}

$testCase = new TeamEvent($container);
$testCase->run();
