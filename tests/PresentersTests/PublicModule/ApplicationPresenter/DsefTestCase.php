<?php

namespace FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter;

use FKSDB\Tests\Events\EventTestCase;
use Nette\Utils\DateTime;
use FKSDB\Modules\PublicModule\ApplicationPresenter;

abstract class DsefTestCase extends EventTestCase {

    protected ApplicationPresenter $fixture;

    protected int $personId;

    protected int $eventId;

    protected function getEventId(): int {
        return $this->eventId;
    }

    protected function setUp(): void {
        parent::setUp();

        $this->eventId = $this->createEvent([
            'event_type_id' => 2,
            'event_year' => 20,
            'registration_end' => new DateTime(date('c', time() + 1000)),
            'parameters' => <<<EOT
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

        $this->personId = $this->createPerson('Paní', 'Bílá', ['email' => 'bila@hrad.cz', 'born' => DateTime::from('2000-01-01')],  []);
    }

    protected function tearDown(): void {
        $this->connection->query('DELETE FROM e_dsef_participant');
        $this->connection->query('DELETE FROM e_dsef_group');
        parent::tearDown();
    }
}
