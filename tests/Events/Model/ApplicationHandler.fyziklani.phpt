<?php

namespace Events\Model;

$container = require '../../bootstrap.php';

use Events\EventTestCase;
use Events\Model\Holder\Holder;
use FKSDB\Events\EventDispatchFactory;
use FKSDB\Logging\DevNullLogger;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\ORM\Services\ServiceEvent;
use MockEnvironment\MockApplicationTrait;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Tester\Assert;

class ApplicationHandlerTest extends EventTestCase {

    use MockApplicationTrait;

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
        parent::__construct($container);
        $this->setContainer($container);
    }

    protected function setUp() {
        parent::setUp();

        $this->connection->query("INSERT INTO event (event_id, event_type_id, year, event_year, begin, end, name)"
            . "                          VALUES (1, 1, 1, 1, '2001-01-02', '2001-01-02', 'Testovací Fyziklání')");

        $this->serviceTeam = $this->getContainer()->getByType(ServiceFyziklaniTeam::class);
        $this->serviceEvent = $this->getContainer()->getByType(ServiceEvent::class);


        $handlerFactory = $this->getContainer()->getByType(ApplicationHandlerFactory::class);
        /** @var ModelEvent $event */
        $event = $this->serviceEvent->findByPrimary(1);
        /** @var EventDispatchFactory $factory */
        $factory =  $this->getContainer()->getByType(EventDispatchFactory::class);
        $this->holder =$factory->getDummyHolder($event);
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
        $id1 = $this->createPerson('Karel', 'Kolář', ['email' => 'k.kolar@email.cz']);

        $id2 = $this->createPerson('Michal', 'Koutný', ['email' => 'michal@fykos.cz']);
        $this->createPersonHistory($id2, 2000, 1, 1);
        $id3 = $this->createPerson('Kristína', 'Nešporová', ['email' => 'kiki@fykos.cz']);
        $this->createPersonHistory($id3, 2000, 1, 1);

        $teamName = '\'); DROP TABLE student; --';

        $data = [
            'team' =>
                [
                    'name' => $teamName,
                    'phone' => '+420987654321',
                    'force_a' => false,
                    'teacher_id' => $id1,
                    'teacher_id_1' =>
                        [
                            '_c_compact' => 'Karel Kolář',
                            'person' =>
                                [
                                    'other_name' => 'Karel',
                                    'family_name' => 'Kolář',
                                ],
                            'person_info' =>
                                [
                                    'email' => 'k.kolar@email.cz',
                                ],
                        ],
                    'teacher_present' => true,
                    'teacher_accomodation' => false,
                ],
            'p1' =>
                [
                    'person_id' => $id2,
                    'person_id_1' =>
                        [
                            '_c_compact' => 'Michal Koutný',
                            'person' =>
                                [
                                    'other_name' => 'Michal',
                                    'family_name' => 'Koutný',
                                ],
                            'person_info' =>
                                [
                                    'email' => 'michal@fykos.cz',
                                    'id_number' => '12345',
                                ],
                            'person_history' =>
                                [
                                    'school_id' => 1,
                                    'study_year' => 2,
                                ],
                        ],
                    'accomodation' => false,
                ],
            'p2' =>
                [
                    'person_id' => $id3,
                    'person_id_1' =>
                        [
                            '_c_compact' => 'Kristína Nešporová',
                            'person' =>
                                [
                                    'other_name' => 'Kristína',
                                    'family_name' => 'Nešporová',
                                ],
                            'person_info' =>
                                [
                                    'email' => 'kiki@fykos.cz',
                                ],
                            'person_history' =>
                                [
                                    'school_id' => 1,
                                    'study_year' => 3,
                                ],
                        ],
                    'accomodation' => false,
                ],
            'p3' =>
                [
                    'person_id' => NULL,
                    'person_id_1' =>
                        [
                            '_c_search' => '',
                            'person' =>
                                [],
                            'person_info' =>
                                [],
                            'person_history' =>
                                [],
                        ],
                    'accomodation' => false,
                ],
            'p4' =>
                [
                    'person_id' => NULL,
                    'person_id_1' =>
                        [
                            '_c_search' => '',
                            'person' =>
                                [],
                            'person_info' =>
                                [],
                            'person_history' =>
                                [],
                        ],
                    'accomodation' => false,
                ],
            'p5' =>
                [
                    'person_id' => NULL,
                    'person_id_1' =>
                        [
                            '_c_search' => '',
                            'person' =>
                                [],
                            'person_info' =>
                                [],
                            'person_history' =>
                                [],
                        ],
                    'accomodation' => false,
                ],
            'privacy' => true,
        ];
        $data = ArrayHash::from($data);
        $this->fixture->storeAndExecute($this->holder, $data);


        $result = $this->serviceTeam->getTable()->where('name', $teamName)->fetch();
        Assert::notEqual(false, $result);

        $team = ModelFyziklaniTeam::createFromActiveRow($result);
        Assert::equal($teamName, $team->name);

        $count = $this->connection->fetchField('SELECT COUNT(1) FROM e_fyziklani_participant WHERE e_fyziklani_team_id = ?', $this->holder->getPrimaryHolder()->getModel()->getPrimary());
        Assert::equal(2, $count);
    }

}

$testCase = new ApplicationHandlerTest($container);
$testCase->run();
