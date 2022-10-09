<?php

declare(strict_types=1);

namespace FKSDB\Tests\Events\Schedule;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\Events\ServiceDsefGroup;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use FKSDB\Models\ORM\Services\Schedule\ScheduleGroupService;
use FKSDB\Models\ORM\Services\Schedule\ScheduleItemService;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Tests\Events\EventTestCase;
use Nette\Application\IPresenter;
use Nette\Application\Request;
use Nette\Utils\DateTime;

abstract class ScheduleTestCase extends EventTestCase
{
    protected ScheduleItemModel $item;
    protected IPresenter $fixture;
    protected ScheduleGroupModel $group;
    protected array $persons = [];
    protected EventModel $event;

    protected function getEvent(): EventModel
    {
        return $this->event;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = $this->createEvent([
            'event_type_id' => 2,
            'event_year' => 24,
            'parameters' => <<<EOT
EOT
            ,
        ]);

        $this->group = $this->getContainer()->getByType(ScheduleGroupService::class)->storeModel([
            'event_id' => $this->event->event_id,
            'schedule_group_type' => 'accommodation',
            'start' => new \DateTime(),
            'end' => new DateTime(),
        ]);
        $this->item = $this->getContainer()->getByType(ScheduleItemService::class)->storeModel([
            'name_cs' => 'Hotel Test',
            'name_en' => 'test hotel',
            'schedule_group_id' => $this->group->schedule_group_id,
            'price_czk' => 20,
            'price_eur' => 30,
            'capacity' => $this->getAccommodationCapacity(),
        ]);

        $this->getContainer()->getByType(ServiceDsefGroup::class)->storeModel([
            'e_dsef_group_id' => 2,
            'event_id' => $this->event->event_id,
            'name' => 'Alpha',
            'capacity' => 4,
        ]);

        $this->fixture = $this->createPresenter('Public:Application');
        $this->mockApplication();

        $this->persons[] = $this->createPerson(
            'Paní',
            'Bílá',
            [
                'email' => 'bila-acc@hrad.cz',
                'born' => DateTime::from('2000-01-01'),
            ]
        );
        $this->getContainer()->getByType(EventParticipantService::class)->storeModel([
            'person_id' => end($this->persons),
            'event_id' => $this->event->event_id,
            'status' => 'applied',
        ]);
        $this->getContainer()->getByType(PersonScheduleService::class)->storeModel([
            'person_id' => end($this->persons),
            'schedule_item_id' => $this->item->schedule_item_id,
        ]);

        $this->persons[] = $this->createPerson(
            'Paní',
            'Bílá II.',
            [
                'email' => 'bila2-acc@hrad.cz',
                'born' => DateTime::from('2000-01-01'),
            ]
        );
        $this->getContainer()->getByType(EventParticipantService::class)->storeModel([
            'person_id' => end($this->persons),
            'event_id' => $this->event->event_id,
            'status' => 'applied',
        ]);
        $this->getContainer()->getByType(PersonScheduleService::class)->storeModel([
            'person_id' => end($this->persons),
            'schedule_item_id' => $this->item->schedule_item_id,
        ]);
    }

    protected function createAccommodationRequest(): Request
    {
        return $this->createPostRequest([
            'participant' => [
                'person_id' => "__promise",
                'person_id_container' => [
                    '_c_compact' => " ",
                    'person' => [
                        'other_name' => "František",
                        'family_name' => "Dobrota",
                    ],
                    'person_info' => [
                        'email' => "ksaadaa@kalo3.cz",
                        'id_number' => "1231354",
                        'born' => "2014-09-15",
                    ],
                    'post_contact_p' => [
                        'address' => [
                            'target' => "jkljhkjh",
                            'city' => "jkhlkjh",
                            'postal_code' => "64546",
                            'country_iso' => "",
                        ],
                    ],
                    'person_schedule' => [
                        'accommodation' => json_encode(
                            [$this->group->schedule_group_id => $this->item->schedule_item_id]
                        ),
                    ],
                ],
                'e_dsef_group_id' => (string)2,
                'lunch_count' => (string)0,
                'message' => "",
            ],
            'privacy' => "on",
            'c_a_p_t_cha' => "pqrt",
            '__init__applied' => "Přihlásit účastníka",
        ]);
    }

    abstract public function getAccommodationCapacity(): int;
}
