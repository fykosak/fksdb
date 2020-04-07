<?php

namespace Events\Model;

use Events\EventTestCase;
use Nette\DI\Container;
use Nette\Utils\DateTime;
use PublicModule\ApplicationPresenter;

abstract class ResourceAvailabilityTestCase extends EventTestCase {

    /**
     * @var ApplicationPresenter
     */
    protected $fixture;
    protected $persons;

    function __construct(Container $container) {
        parent::__construct($container);
        $this->setContainer($container);
    }

    abstract function getCapacity();

    protected function setUp() {
        parent::setUp();

        $capacity = $this->getCapacity();
        $this->eventId = $this->createEvent(array(
            'event_type_id' => 2,
            'event_year' => 20,
            'registration_end' => new DateTime(date('c', time() + 1000)),
            'parameters' => <<<EOT
accomodationCapacity: $capacity
EOT
        ));

        $this->insert('e_dsef_group', array(
            'e_dsef_group_id' => 1,
            'event_id' => $this->eventId,
            'name' => 'Alpha',
            'capacity' => 4
        ));


        $this->fixture = $this->createPresenter('Public:Application');
        $this->mockApplication();

        $this->persons = array();
        $this->persons[] = $this->createPerson('Paní', 'Bílá', array('email' => 'bila@hrad.cz', 'born' => DateTime::from('2000-01-01')));
        $eid = $this->insert('event_participant', array(
            'person_id' => end($this->persons),
            'event_id' => $this->eventId,
            'status' => 'applied',
            'accomodation' => 1,
        ));
        $this->insert('e_dsef_participant', array(
            'event_participant_id' => $eid,
            'e_dsef_group_id' => 1,
        ));

        $this->persons[] = $this->createPerson('Paní', 'Bílá II.', array('email' => 'bila2@hrad.cz', 'born' => DateTime::from('2000-01-01')));
        $eid = $this->insert('event_participant', array(
            'person_id' => end($this->persons),
            'event_id' => $this->eventId,
            'status' => 'applied',
            'accomodation' => 1,
        ));
        $this->insert('e_dsef_participant', array(
            'event_participant_id' => $eid,
            'e_dsef_group_id' => 1,
        ));
    }

    protected function tearDown() {
        $this->connection->query("DELETE FROM e_dsef_participant");
        $this->connection->query("DELETE FROM e_dsef_group");
        parent::tearDown();
    }

}

