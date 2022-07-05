<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter;

use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Services\Schedule\ServicePersonSchedule;
use FKSDB\Models\ORM\Services\Schedule\ServiceScheduleGroup;
use FKSDB\Models\ORM\Services\Schedule\ServiceScheduleItem;
use FKSDB\Models\ORM\Services\ServiceEventParticipant;
use FKSDB\Tests\Events\EventTestCase;
use Nette\Utils\DateTime;
use FKSDB\Modules\PublicModule\ApplicationPresenter;

abstract class DsefTestCase extends EventTestCase
{

    protected ApplicationPresenter $fixture;
    protected ModelPerson $person;
    protected ModelEvent $event;

    protected function getEvent(): ModelEvent
    {
        return $this->event;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = $this->createEvent([
            'event_type_id' => 2,
            'event_year' => 20,
            'registration_end' => new \DateTime(date('c', time() + 1000)),
            'parameters' => <<<EOT
EOT
            ,
        ]);

        $this->group = $this->getContainer()->getByType(ServiceScheduleGroup::class)->createNewModel([
            'event_id' => $this->event->event_id,
            'schedule_group_type' => 'dsef_morning',
            'start' => new \DateTime(),
            'end' => new \DateTime(),
        ]);
        $this->item = $this->getContainer()->getByType(ServiceScheduleItem::class)->createNewModel([
            'name_cs' => 'alpha',
            'name_en' => 'alpha',
            'schedule_group_id' => $this->group->schedule_group_id,
            'price_czk' => 20,
            'price_eur' => 30,
            'capacity' => 7,
        ]);

        $this->fixture = $this->createPresenter('Public:Application');
        $this->mockApplication();

        $this->person = $this->createPerson(
            'Paní',
            'Bílá',
            ['email' => 'bila@hrad.cz', 'born' => DateTime::from('2000-01-01')],
            []
        );

        $this->getContainer()->getByType(ServicePersonSchedule::class)->createNewModel([
            'person_id' => $this->person->person_id,
            'schedule_item_id' => $this->item->schedule_item_id,
        ]);

        $this->getContainer()->getByType(ServiceEventParticipant::class)->createNewModel([
            'person_id' => $this->person->person_id,
            'event_id' => $this->event->event_id,
            'status' => 'applied',
        ]);
    }
}
