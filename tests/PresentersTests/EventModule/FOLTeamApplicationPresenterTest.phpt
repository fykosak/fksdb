<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\EventModule;

// phpcs:disable
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;

$container = require '../../Bootstrap.php';

// phpcs:enable

class FOLTeamApplicationPresenterTest extends TeamApplicationPresenterTest
{

    protected function createEvent(): EventModel
    {
        return $this->getContainer()->getByType(EventService::class)->storeModel([
            'event_type_id' => 9,
            'year' => 1,
            'event_year' => 1,
            'begin' => new \DateTime(),
            'end' => new \DateTime(),
            'registration_begin' => (new \DateTime())->sub(new \DateInterval('P1D')),
            'registration_end' => (new \DateTime())->add(new \DateInterval('P1D')),
            'name' => 'Test FOL opened',
        ]);
    }
}

// phpcs:disable
$testCase = new FOFTeamApplicationPresenterTest($container);
$testCase->run();
// phpcs:enable
