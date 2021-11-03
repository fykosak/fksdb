<?php

declare(strict_types=1);

namespace FKSDB\Tests\Events\Model;

$container = require '../../Bootstrap.php';

use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Events\Model\ApplicationHandler;
use FKSDB\Models\Events\Model\ApplicationHandlerException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\YearCalculator;
use FKSDB\Tests\Events\EventTestCase;
use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\Models\ORM\Services\ServiceEvent;
use FKSDB\Tests\MockEnvironment\MockApplicationTrait;
use Fykosak\Utils\Logging\DevNullLogger;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Tester\Assert;

class ApplicationHandlerTest extends EventTestCase
{
    use MockApplicationTrait;

    private ApplicationHandler $fixture;

    private ServiceFyziklaniTeam $serviceTeam;

    private Holder $holder;

    /**
     * ApplicationHandlerTest constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->setContainer($container);
        $this->serviceTeam = $this->getContainer()->getByType(ServiceFyziklaniTeam::class);
    }

    protected function getEventId(): int
    {
        throw new BadRequestException();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->explorer->query(
            "INSERT INTO event (event_id, event_type_id, year, event_year, begin, end, name)"
            . "                          VALUES (1, 1, 1, 1, '2001-01-02', '2001-01-02', 'Testovací Fyziklání')"
        );

        /** @var ServiceEvent $serviceEvent */
        $serviceEvent = $this->getContainer()->getByType(ServiceEvent::class);

        /** @var ModelEvent $event */
        $event = $serviceEvent->findByPrimary(1);
        /** @var EventDispatchFactory $factory */
        $factory = $this->getContainer()->getByType(EventDispatchFactory::class);
        $this->holder = $factory->getDummyHolder($event);
        $this->fixture = new ApplicationHandler($event, new DevNullLogger(), $this->getContainer());

        $this->mockApplication();
    }

    protected function tearDown(): void
    {
        $this->truncateTables([DbNames::TAB_E_FYZIKLANI_PARTICIPANT, DbNames::TAB_E_FYZIKLANI_TEAM]);
        parent::tearDown();
    }

    /**
     * This test doesn't test much, at least it detects weird data passing in CategoryProcessing.
     */
    public function testNewApplication(): void
    {
        $id1 = $this->createPerson('Karel', 'Kolář', ['email' => 'k.kolar@email.cz']);

        $id2 = $this->createPerson('Michal', 'Koutný', ['email' => 'michal@fykos.cz']);
        $this->createPersonHistory($id2, YearCalculator::getCurrentAcademicYear(), 1, 1);
        $id3 = $this->createPerson('Kristína', 'Nešporová', ['email' => 'kiki@fykos.cz']);
        $this->createPersonHistory($id3, YearCalculator::getCurrentAcademicYear(), 1, 1);

        $teamName = '\'); DROP TABLE student; --';

        $data = [
            'team' =>
                [
                    'name' => $teamName,
                    'phone' => '+420987654321',
                    'force_a' => false,
                    'teacher_id' => (string)$id1,
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
                    'person_id' => (string)$id2,
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
                    'person_id' => (string)$id3,
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
                    'person_id' => null,
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
                    'person_id' => null,
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
                    'person_id' => null,
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
        Assert::exception(function () use ($data, $teamName) {
            $this->fixture->storeAndExecuteValues($this->holder, $data);
            /** @var ModelFyziklaniTeam $team */
            $team = $this->serviceTeam->getTable()->where('name', $teamName)->fetch();
            Assert::notEqual(false, $team);

            Assert::equal($teamName, $team->name);

            $count = $this->explorer->fetchField(
                'SELECT COUNT(1) FROM e_fyziklani_participant WHERE e_fyziklani_team_id = ?',
                $this->holder->getPrimaryHolder()->getModel2()->getPrimary()
            );
            Assert::equal(2, $count);
        }, ApplicationHandlerException::class);
    }
}

$testCase = new ApplicationHandlerTest($container);
$testCase->run();
