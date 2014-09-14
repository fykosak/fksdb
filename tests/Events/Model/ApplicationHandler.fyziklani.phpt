<?php

namespace Events\Model;

$container = require '../../bootstrap.php';

use BasePresenter;
use DatabaseTestCase;
use Events\Model\Holder\Holder;
use FKS\Logging\DevNullLogger;
use Nette\ArrayHash;
use Nette\DI\Container;
use ORM\Models\Events\ModelFyziklaniTeam;
use ORM\Services\Events\ServiceFyziklaniTeam;
use ServiceEvent;
use Tester\Assert;

class ApplicationHandlerTest extends DatabaseTestCase {

    /**
     * @var Container
     */
    private $container;

    /**
     * @var ApplicationHandler
     */
    private $fixture;

    /**
     * @var ServiceEvent
     */
    private $serviceEvent;

    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceTeam;

    /**
     * @var Holder
     */
    private $holder;

    function __construct(Container $container) {
        parent::__construct($container->getService('nette.database.default'));
        $this->container = $container;
    }

    protected function setUp() {
        parent::setUp();
        $this->connection->query("INSERT INTO event_type (event_type_id, contest_id, name) VALUES (1, 1, 'Fyziklání')");
        $this->connection->query("INSERT INTO event (event_id, event_type_id, year, event_year, begin, end, name)"
                . "                          VALUES (1, 1, 1, 1, '2001-01-02', '2001-01-02', 'Testovací Fyziklání')");
        $this->connection->query("INSERT INTO event_status (status) VALUES ('pending'), ('spare'), ('approved'), ('participated'), ('cancelled'), ('missed'), ('applied')");

        $this->serviceTeam = $this->container->getService('event.ServiceFyziklaniTeam');
        $this->serviceEvent = $this->container->getService('ServiceEvent');


        $handlerFactory = $this->container->getByType('Events\Model\ApplicationHandlerFactory');
        $event = $this->serviceEvent->findByPrimary(1);
        $this->holder = $this->container->createEventHolder($event);
        $this->fixture = $handlerFactory->create($event, new DevNullLogger());

        $mockPresenter = new MockPresenter($this->container);
        $this->container->callMethod(array($mockPresenter, 'injectTranslator'));
        $application = new MockApplication($mockPresenter);

        $mailFactory = $this->container->getByType('Mail\MailTemplateFactory');
        $mailFactory->injectApplication($application);
    }

    protected function tearDown() {
        $this->connection->query("DELETE FROM e_fyziklani_participant");
        $this->connection->query("DELETE FROM event_participant");
        $this->connection->query("DELETE FROM e_fyziklani_team");
        $this->connection->query("DELETE FROM event_status");
        $this->connection->query("DELETE FROM event");
        $this->connection->query("DELETE FROM event_type");
        $this->connection->query("DELETE FROM auth_token");
        $this->connection->query("DELETE FROM login");

        parent::tearDown();
    }

    /**
     * This test doesn't test much, at least it detects weird data passing in CategoryProcessing.
     * @throws Events\Model\ApplicationHandlerException
     */
    public function testNewApplication() {
        $id1 = $this->createPerson('Karel', 'Kolář', 'k.kolar@email.cz');

        $id2 = $this->createPerson('Michal', 'Koutný', 'michal@fykos.cz');
        $this->createPersonHistory($id2, 2000, 1, null, 1);
        $id3 = $this->createPerson('Kristína', 'Nešporová', 'kiki@fykos.cz');
        $this->createPersonHistory($id3, 2000, 1, null, 1);

        $teamName = '\'); DROP TABLE student; --';
        
        $data = array(
            'team' =>
            array(
                'name' => $teamName,
                'phone' => '',
                'teacher_id' => $id1,
                'teacher_id_1' =>
                array(
                    '_c_compact' => 'Karel Kolář',
                    'person' =>
                    array(
                        'other_name' => 'Karel',
                        'family_name' => 'Kolář',
                    ),
                    'person_info' =>
                    array(
                        'email' => 'k.kolar@email.cz',
                    ),
                ),
                'teacher_present' => true,
                'teacher_accomodation' => false,
            ),
            'p1' =>
            array(
                'person_id' => $id2,
                'person_id_1' =>
                array(
                    '_c_compact' => 'Michal Koutný',
                    'person' =>
                    array(
                        'other_name' => 'Michal',
                        'family_name' => 'Koutný',
                    ),
                    'person_info' =>
                    array(
                        'email' => 'michal@fykos.cz',
                        'id_number' => '12345',
                    ),
                    'person_history' =>
                    array(
                        'school_id' => 1,
                        'study_year' => 2,
                    ),
                ),
                'accomodation' => false,
            ),
            'p2' =>
            array(
                'person_id' => $id3,
                'person_id_1' =>
                array(
                    '_c_compact' => 'Kristína Nešporová',
                    'person' =>
                    array(
                        'other_name' => 'Kristína',
                        'family_name' => 'Nešporová',
                    ),
                    'person_info' =>
                    array(
                        'email' => 'kiki@fykos.cz',
                    ),
                    'person_history' =>
                    array(
                        'school_id' => 1,
                        'study_year' => 3,
                    ),
                ),
                'accomodation' => false,
            ),
            'p3' =>
            array(
                'person_id' => NULL,
                'person_id_1' =>
                array(
                    '_c_search' => '',
                    'person' =>
                    array(),
                    'person_info' =>
                    array(),
                    'person_history' =>
                    array(),
                ),
                'accomodation' => false,
            ),
            'p4' =>
            array(
                'person_id' => NULL,
                'person_id_1' =>
                array(
                    '_c_search' => '',
                    'person' =>
                    array(),
                    'person_info' =>
                    array(),
                    'person_history' =>
                    array(),
                ),
                'accomodation' => false,
            ),
            'p5' =>
            array(
                'person_id' => NULL,
                'person_id_1' =>
                array(
                    '_c_search' => '',
                    'person' =>
                    array(),
                    'person_info' =>
                    array(),
                    'person_history' =>
                    array(),
                ),
                'accomodation' => false,
            ),
            'privacy' => true,
        );
        $data = ArrayHash::from($data);
        $this->fixture->storeAndExecute($this->holder, $data);

        Assert::true(true);
        $result = $this->serviceTeam->getTable()->where('name', $teamName)->fetch();
        Assert::notEqual(false, $result);

        $team = ModelFyziklaniTeam::createFromTableRow($result);
        Assert::equal($teamName, $team->name);

        $count = $this->connection->fetchField('SELECT COUNT(1) FROM e_fyziklani_participant WHERE e_fyziklani_team_id = ?', $this->holder->getPrimaryHolder()->getModel()->getPrimary());
        Assert::equal(2, $count);
    }

}

/*
 * Mock classes
 */

class MockApplication {

    /**
     * @var BasePresenter
     */
    private $presenter;

    public function __construct(BasePresenter $presenter) {
        $this->presenter = $presenter;
    }

    public function getPresenter() {
        return $this->presenter;
    }

}

class MockPresenter extends BasePresenter {

    public function link($destination, $args = array()) {
        return '';
    }

    public function getLang() {
        return 'cs';
    }

}

$testCase = new ApplicationHandlerTest($container);
$testCase->run();
