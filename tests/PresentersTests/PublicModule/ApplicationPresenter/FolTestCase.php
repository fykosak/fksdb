<?php

namespace FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter;

use FKSDB\Tests\Events\EventTestCase;
use Nette\Database\Row;
use Nette\Utils\DateTime;
use FKSDB\Modules\PublicModule\ApplicationPresenter;
use Tester\Assert;

abstract class FolTestCase extends EventTestCase {

    /**
     * @var ApplicationPresenter
     */
    protected $fixture;
    /** @var int */
    protected $personId;
    /**
     * @var int
     */
    protected $eventId;

    protected function getEventId(): int {
        return $this->eventId;
    }

    protected function setUp() {
        parent::setUp();

        $future = DateTime::from(time() + DateTime::DAY);
        $this->eventId = $this->createEvent([
            'event_type_id' => 9,
            'event_year' => 4,
            'begin' => $future,
            'end' => $future,
            'parameters' => <<<EOT
EOT
            ,
        ]);

        $this->fixture = $this->createPresenter('Public:Application');
        $this->mockApplication();

        $this->personId = $this->createPerson('Paní', 'Bílá', ['email' => 'bila@hrad.cz', 'born' => DateTime::from('2000-01-01')], true);
    }

    protected function tearDown() {
        $this->connection->query('DELETE FROM e_fyziklani_participant');
        $this->connection->query('DELETE FROM e_fyziklani_team');
        parent::tearDown();
    }

    protected function assertTeamApplication(int $eventId, string $teamName): Row {
        $application = $this->connection->fetch('SELECT * FROM e_fyziklani_team WHERE event_id = ? AND name = ?', $eventId, $teamName);
        Assert::notEqual(false, $application);
        return $application;
    }

}
