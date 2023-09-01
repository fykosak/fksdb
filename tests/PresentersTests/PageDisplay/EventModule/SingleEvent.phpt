<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PageDisplay\EventModule;

// phpcs:disable
$container = require '../../../Bootstrap.php';

// phpcs:enable
use DateTime;

class SingleEvent extends EventModuleTestCase
{
    protected function getEventData(): array
    {
        return [
            'event_type_id' => 7,
            'year' => 1,
            'event_year' => 1,
            'begin' => new DateTime(),
            'end' => new DateTime(),
            'name' => 'TEST TSAF',
            'registration_begin' => new \DateTime(),
            'registration_end' => new \DateTime(),
        ];
    }

    public function getPages(): array
    {
        return [
            ['Event:Application', 'list'],
            ['Event:Application', 'import'],
            ['Event:Application', 'mass'],
           // ['Event:Application', 'attendance'],
            ['Event:Chart', 'list'],
            ['Event:Dashboard', 'default'],
            ['Event:Dispatch', 'default'],
            ['Event:EventOrg', 'list'],
            ['Event:EventOrg', 'create'],
        ];
    }
}

// phpcs:disable
$testCase = new SingleEvent($container);
$testCase->run();
// phpcs:enable
