<?php

use Nette\Utils\DateTime;

abstract class ApplicationPresenterTsafTestCase extends ApplicationPresenterDsefTestCase {

    protected $dsefEventId;
    protected $tsafEventId;

    protected function setUp() {
        parent::setUp();
        $this->dsefEventId = $this->eventId;

        $this->tsafEventId = $this->createEvent([
            'event_type_id' => 7,
            'event_year' => 7,
            'registration_end' => new DateTime(date('c', time() + 1000)),
            'parameters' => <<<EOT
capacity: 5
EOT
        ]);
    }

    protected function tearDown() {
        $this->connection->query("DELETE FROM e_tsaf_participant");
        $this->connection->query("DELETE FROM e_dsef_participant");
        parent::tearDown();
    }

}
