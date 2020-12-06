<?php

namespace FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter;

use Nette\Utils\DateTime;
use Tester\Environment;

abstract class TsafTestCase extends DsefTestCase {

    protected int $dsefEventId;

    protected int $tsafEventId;

    protected function getEventId(): int {
        return $this->eventId;
    }

    protected function setUp(): void {
        Environment::skip('3.0');
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

    protected function tearDown(): void {
        $this->connection->query('DELETE FROM e_dsef_participant');
        parent::tearDown();
    }
}
