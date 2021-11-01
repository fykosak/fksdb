<?php

declare(strict_types=1);

namespace FKSDB\Tests\Events\FormAdjustments;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Tests\Events\EventTestCase;
use Nette\Application\IPresenter;
use Nette\Utils\DateTime;

abstract class ResourceAvailabilityTestCase extends EventTestCase
{

    protected IPresenter $fixture;
    protected array $persons = [];
    protected int $eventId;

    abstract protected function getCapacity(): int;

    protected function getEventId(): int
    {
        return $this->eventId;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $capacity = $this->getCapacity();
        $this->eventId = $this->createEvent([
            'event_type_id' => 2,
            'event_year' => 20,
            'registration_end' => new DateTime(date('c', time() + 1000)),
            'parameters' => <<<EOT
accomodationCapacity: $capacity
EOT
            ,
        ]);

        $this->insert('e_dsef_group', [
            'e_dsef_group_id' => 1,
            'event_id' => $this->eventId,
            'name' => 'Alpha',
            'capacity' => 4,
        ]);

        $this->fixture = $this->createPresenter('Public:Application');
        $this->mockApplication();

        $this->persons = [];
        $this->persons[] = $this->createPerson(
            'Paní',
            'Bílá',
            ['email' => 'bila@hrad.cz', 'born' => DateTime::from('2000-01-01')]
        );
        $eid = $this->insert('event_participant', [
            'person_id' => end($this->persons),
            'event_id' => $this->eventId,
            'status' => 'applied',
            'accomodation' => 1,
        ]);
        $this->insert('e_dsef_participant', [
            'event_participant_id' => $eid,
            'e_dsef_group_id' => 1,
        ]);

        $this->persons[] = $this->createPerson(
            'Paní',
            'Bílá II.',
            ['email' => 'bila2@hrad.cz', 'born' => DateTime::from('2000-01-01')]
        );
        $eid = $this->insert('event_participant', [
            'person_id' => end($this->persons),
            'event_id' => $this->eventId,
            'status' => 'applied',
            'accomodation' => 1,
        ]);
        $this->insert('e_dsef_participant', [
            'event_participant_id' => $eid,
            'e_dsef_group_id' => 1,
        ]);
    }

    protected function tearDown(): void
    {
        $this->truncateTables([
            DbNames::TAB_E_DSEF_PARTICIPANT,
            DbNames::TAB_E_DSEF_GROUP,
            DbNames::TAB_EVENT_PARTICIPANT,
            DbNames::TAB_EVENT,
        ]);
        parent::tearDown();
    }
}
