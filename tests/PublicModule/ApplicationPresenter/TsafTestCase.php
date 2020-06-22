<?php

namespace FKSDB\Tests\PublicModule\ApplicationPresenter;

use Nette\Utils\DateTime;

abstract class TsafTestCase extends DsefTestCase {
    /** @var int */
    protected $dsefEventId;
    /** @var int */
    protected $tsafEventId;

    protected function getEventId(): int {
        return $this->eventId;
    }

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
            ,
        ]);
    }

    protected function tearDown() {
        $this->connection->query('DELETE FROM e_tsaf_participant');
        $this->connection->query('DELETE FROM e_dsef_participant');
        parent::tearDown();
    }
}
