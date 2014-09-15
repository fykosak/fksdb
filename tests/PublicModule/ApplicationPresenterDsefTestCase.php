<?php

use Events\EventTestCase;
use Nette\DateTime;
use Nette\DI\Container;
use PublicModule\ApplicationPresenter;

abstract class ApplicationPresenterDsefTestCase extends EventTestCase {

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var ApplicationPresenter
     */
    protected $fixture;
    protected $personId;

    function __construct(Container $container) {
        parent::__construct($container->getService('nette.database.default'));
        $this->container = $container;
    }

    protected function setUp() {
        parent::setUp();

        $this->eventId = $this->createEvent(array(
            'event_type_id' => 2,
            'event_year' => 20,
            'parameters' => <<<EOT
groups:
    Alpha: 2
    Bravo: 2
EOT
        ));

        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $this->fixture = $presenterFactory->createPresenter('Public:Application');
        $this->fixture->autoCanonicalize = false;

        $this->container->getByType('Authentication\LoginUserStorage')->setPresenter($this->fixture);

        $this->mockApplication($this->container);

        $this->personId = $this->createPerson('Paní', 'Bílá', array('email' => 'bila@hrad.cz', 'born' => DateTime::from('2000-01-01')), true);
    }

    protected function tearDown() {
        $this->connection->query("DELETE FROM e_dsef_participant");
        parent::tearDown();
    }

}
