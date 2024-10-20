<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PageDisplay\EventModule;

// phpcs:disable
$container = require '../../../Bootstrap.php';

// phpcs:enable

class SingleEvent extends EventModuleTestCase
{
    protected function getEventData(): array
    {
        return [
            'event_type_id' => 2,
            'year' => 1,
            'event_year' => 1,
            'begin' => new \DateTime(),
            'end' => new \DateTime(),
            'name' => 'TEST DSEF',
            'registration_begin' => new \DateTime(),
            'registration_end' => new \DateTime(),
            'parameters' => 'hashSalt: abcdefgh'
        ];
    }

    public function getPages(): array
    {
        return [
            ['Event:Chart', 'list'],
            ['Event:Dashboard', 'default'],
            ['Event:Dispatch', 'default'],
            ['Event:EventOrganizer', 'list'],
            ['Event:EventOrganizer', 'create'],

            ['Event:Application', 'default'],
            ['Event:Application', 'create'],
            ['Event:Application', 'import'],
            ['Event:Application', 'mass'],
        ];
    }
}

// phpcs:disable
$testCase = new SingleEvent($container);
$testCase->run();
// phpcs:enable
