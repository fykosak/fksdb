<?php

use Events\EventTestCase;
use Nette\DateTime;
use Nette\DI\Container;
use PublicModule\ApplicationPresenter;
use Tester\Assert;

abstract class ApplicationPresenterFolTestCase extends EventTestCase {

    /**
     * @var ApplicationPresenter
     */
    protected $fixture;
    protected $personId;

    function __construct(Container $container) {
        parent::__construct($container);
        $this->setContainer($container);
    }

    protected function setUp() {
        parent::setUp();

        $future = DateTime::from(time() + DateTime::DAY);
        $this->eventId = $this->createEvent(array(
            'event_type_id' => 9,
            'event_year' => 4,
            'begin' => $future,
            'end' => $future,
            'parameters' => <<<EOT
EOT
        ));

        $this->fixture = $this->createPresenter('Public:Application');
        $this->mockApplication();

        $this->personId = $this->createPerson('Paní', 'Bílá', array('email' => 'bila@hrad.cz', 'born' => DateTime::from('2000-01-01')), true);
    }

    protected function tearDown() {
        $this->connection->query("DELETE FROM e_fyziklani_participant");
        $this->connection->query("DELETE FROM e_fyziklani_team");
        parent::tearDown();
    }

    protected function assertTeamApplication($eventId, $teamName) {
        $application = $this->connection->fetch('SELECT * FROM e_fyziklani_team WHERE event_id = ? AND name = ?', $eventId, $teamName);
        Assert::notEqual(false, $application);
        return $application;
    }

}
