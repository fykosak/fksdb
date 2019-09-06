<?php

namespace Events\Accommodation;


use Events\EventTestCase;
use Nette\DI\Container;
use Nette\Utils\DateTime;

abstract class ScheduleTestCase extends EventTestCase {
    protected $itemId;
    protected $fixture;
    protected $groupId;

    /**
     * AccommodationTestCase constructor.
     * @param Container $container
     */
    function __construct(Container $container) {
        parent::__construct($container);
        $this->setContainer($container);
    }

    //protected $persons = [];

    protected function setUp() {
        parent::setUp();

        $this->eventId = $this->createEvent([
            'event_type_id' => 2,
            'event_year' => 24,
            'parameters' => <<<EOT
EOT
        ]);


        $this->groupId = $this->insert('schedule_group', [
            'event_id' => $this->eventId,
            'schedule_group_type' => 'accommodation',
            'start' => new \DateTime(),
            'end' => new DateTime(),
        ]);
        $this->itemId = $this->insert('schedule_item', [
            'name_cs' => 'Hotel Test',
            'name_en' => 'test hotel',
            'schedule_group_id' => $this->groupId,
            'price_czk' => 20,
            'price_eur' => 30,
            'capacity' => $this->getAccommodationCapacity(),
        ]);

        $this->insert('e_dsef_group', [
            'e_dsef_group_id' => 2,
            'event_id' => $this->eventId,
            'name' => 'Alpha',
            'capacity' => 4
        ]);


        $this->fixture = $this->createPresenter('Public:Application');
        $this->mockApplication();


        $this->persons[] = $this->createPerson('Paní', 'Bílá',
            [
                'email' => 'bila-acc@hrad.cz',
                'born' => DateTime::from('2000-01-01'),
            ]);
        $this->insert('event_participant', [
            'person_id' => end($this->persons),
            'event_id' => $this->eventId,
            'status' => 'applied',
        ]);
        $this->insert('person_schedule', [
            'person_id' => end($this->persons),
            'schedule_item_id' => $this->itemId,
        ]);


        $this->persons[] = $this->createPerson('Paní', 'Bílá II.',
            [
                'email' => 'bila2-acc@hrad.cz',
                'born' => DateTime::from('2000-01-01'),
            ]);
        $this->insert('event_participant', [
            'person_id' => end($this->persons),
            'event_id' => $this->eventId,
            'status' => 'applied',
        ]);
        $this->insert('person_schedule', [
            'person_id' => end($this->persons),
            'schedule_item_id' => $this->itemId,
        ]);
    }

    protected function createAccommodationRequest() {
        $request = $this->createPostRequest([
            'participant' => [
                'person_id' => "__promise",
                'person_id_1' => [
                    '_c_compact' => " ",
                    'person' => [
                        'other_name' => "František",
                        'family_name' => "Dobrota",
                    ],
                    'person_info' => [
                        'email' => "ksaadaa@kalo3.cz",
                        'id_number' => "1231354",
                        'born' => "15. 09. 2014",
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
                        'accommodation' => json_encode([$this->groupId => $this->itemId]),
                    ],
                ],
                'e_dsef_group_id' => 2,
                'lunch_count' => 0,
                'message' => "",
            ],
            'privacy' => "on",
            'c_a_p_t_cha' => "pqrt",
            '__init__applied' => "Přihlásit účastníka",
        ]);
        return $request;
    }

    abstract public function getAccommodationCapacity();

    protected function tearDown() {
        $this->connection->query("DELETE FROM e_dsef_participant");
        $this->connection->query("DELETE FROM e_dsef_group");
        $this->connection->query("DELETE FROM person_schedule");
        $this->connection->query("DELETE FROM schedule_item");
        $this->connection->query("DELETE FROM schedule_group");
        parent::tearDown();
    }
}
