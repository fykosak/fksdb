<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\EventModule;

// phpcs:disable
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\EventService;

$container = require '../../Bootstrap.php';

// phpcs:enable

class FOFTeamApplicationPresenterTest extends TeamApplicationPresenterTest
{
    private PersonModel $teacherA;
    private PersonModel $teacherB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();

        $this->teacherA = $this->createPerson(
            'TA',
            'TAS',
            ['email' => 'a@skola.a'],
            ['login' => 'TAAAAAA', 'hash' => 'TAAAAAA']
        );
        $this->teacherB = $this->createPerson(
            'TB',
            'TBS',
            ['email' => 'b@skola.a'],
            ['login' => 'TAAAAAA', 'hash' => 'TAAAAAA']
        );
    }

    protected function createEvent(): EventModel
    {
        return $this->getContainer()->getByType(EventService::class)->storeModel([
            'event_type_id' => 1,
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
