<?php

namespace Events\Model;

$container = require '../../bootstrap.php';

use Events\EventTestCase;
use Events\Model\Holder\Holder;
use FKSDB\Logging\DevNullLogger;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;

use MockEnvironment\MockApplicationTrait;
use Nette\Utils\ArrayHash;
use Nette\DI\Container;
use Tester\Assert;

class ApplicationHandlerTest extends EventTestCase {

    use MockApplicationTrait;

    /**
     * @var ApplicationHandler
     */
    private $fixture;

    /**
     * @var \FKSDB\ORM\Services\ServiceEvent
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
        parent::__construct($container);
        $this->setContainer($container);
    }

    protected function setUp() {
        parent::setUp();

        $this->connection->query("INSERT INTO event (event_id, event_type_id, year, event_year, begin, end, name)"
                . "                          VALUES (1, 1, 1, 1, '2001-01-02', '2001-01-02', 'Testovací Fyziklání')");

        $this->serviceTeam = $this->getContainer()->getService('fyziklani.ServiceFyziklaniTeam');
        $this->serviceEvent = $this->getContainer()->getService('ServiceEvent');


        $handlerFactory = $this->getContainer()->getByType('Events\Model\ApplicationHandlerFactory');
        $event = $this->serviceEvent->findByPrimary(1);
        $this->holder = $this->getContainer()->createEventHolder($event);
        $this->fixture = $handlerFactory->create($event, new DevNullLogger());

        $this->mockApplication();
    }

    protected function tearDown() {
        $this->connection->query("DELETE FROM e_fyziklani_participant");
        $this->connection->query("DELETE FROM e_fyziklani_team");

        parent::tearDown();
    }

    /**
     * This test doesn't test much, at least it detects weird data passing in CategoryProcessing.
     * @throws Events\Model\ApplicationHandlerException
     */
    public function testNewApplication() {
        $id1 = $this->createPerson('Karel', 'Kolář', array('email' => 'k.kolar@email.cz'));

        $id2 = $this->createPerson('Michal', 'Koutný', array('email' => 'michal@fykos.cz'));
        $this->createPersonHistory($id2, 2000, 1, 1);
        $id3 = $this->createPerson('Kristína', 'Nešporová', array('email' => 'kiki@fykos.cz'));
        $this->createPersonHistory($id3, 2000, 1, 1);

        $teamName = '\'); DROP TABLE student; --';

        $data = array(
            'team' =>
            array(
                'name' => $teamName,
                'phone' => '',
                'force_a' => false,
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


        $result = $this->serviceTeam->getTable()->where('name', $teamName)->fetch();
        Assert::notEqual(false, $result);

        $team = ModelFyziklaniTeam::createFromTableRow($result);
        Assert::equal($teamName, $team->name);

        $count = $this->connection->fetchField('SELECT COUNT(1) FROM e_fyziklani_participant WHERE e_fyziklani_team_id = ?', $this->holder->getPrimaryHolder()->getModel()->getPrimary());
        Assert::equal(2, $count);
    }

}

$testCase = new ApplicationHandlerTest($container);
$testCase->run();
