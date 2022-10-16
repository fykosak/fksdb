<?php

declare(strict_types=1);

namespace FKSDB\Tests\Events\FormAdjustments;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\Events\ServiceDsefGroup;
use FKSDB\Models\ORM\Services\Events\ServiceDsefParticipant;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Tests\Events\EventTestCase;
use Nette\Application\IPresenter;
use Nette\Utils\DateTime;

abstract class ResourceAvailabilityTestCase extends EventTestCase
{
    protected IPresenter $fixture;
    protected array $persons = [];
    protected EventModel $event;

    abstract protected function getCapacity(): int;

    protected function getEvent(): EventModel
    {
        return $this->event;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $capacity = $this->getCapacity();
        $this->event = $this->createEvent([
            'event_type_id' => 2,
            'event_year' => 20,
            'registration_end' => new \DateTime(date('c', time() + 1000)),
            'parameters' => <<<EOT
accomodationCapacity: $capacity
EOT
            ,
        ]);

        $this->getContainer()->getByType(ServiceDsefGroup::class)->storeModel([
            'e_dsef_group_id' => 1,
            'event_id' => $this->event->event_id,
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
        $application = $this->getContainer()->getByType(EventParticipantService::class)->storeModel([
            'person_id' => end($this->persons),
            'event_id' => $this->event->event_id,
            'status' => 'applied',
            'accomodation' => 1,
        ]);
        $this->getContainer()->getByType(ServiceDsefParticipant::class)->storeModel([
            'event_participant_id' => $application->event_participant_id,
            'e_dsef_group_id' => 1,
        ]);

        $this->persons[] = $this->createPerson(
            'Paní',
            'Bílá II.',
            ['email' => 'bila2@hrad.cz', 'born' => DateTime::from('2000-01-01')]
        );
        $application = $this->getContainer()->getByType(EventParticipantService::class)->storeModel([
            'person_id' => end($this->persons),
            'event_id' => $this->event->event_id,
            'status' => 'applied',
            'accomodation' => 1,
        ]);
        $this->getContainer()->getByType(ServiceDsefParticipant::class)->storeModel([
            'event_participant_id' => $application->event_participant_id,
            'e_dsef_group_id' => 1,
        ]);
    }
}
