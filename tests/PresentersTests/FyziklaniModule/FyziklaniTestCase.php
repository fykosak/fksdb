<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\FyziklaniModule;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTask;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniGameSetup;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\Models\ORM\Services\ServiceEvent;
use FKSDB\Models\ORM\Services\ServiceOrg;
use FKSDB\Tests\MockEnvironment\MockApplicationTrait;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\DI\Container;
use Nette\Utils\DateTime;

abstract class FyziklaniTestCase extends DatabaseTestCase
{
    use MockApplicationTrait;

    protected ModelEvent $event;
    protected ModelPerson $userPerson;

    /**
     * FyziklaniTestCase constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->setContainer($container);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->userPerson = $this->createPerson(
            'Paní',
            'Černá',
            ['email' => 'cerna@hrad.cz', 'born' => DateTime::from('2000-01-01')],
            []
        );
        $this->getContainer()->getByType(ServiceOrg::class)->createNewModel(
            ['person_id' => $this->userPerson->person_id, 'contest_id' => 1, 'since' => 0, 'order' => 0]
        );
    }

    protected function tearDown(): void
    {
        $this->truncateTables(
            ['fyziklani_submit', 'fyziklani_task', DbNames::TAB_E_FYZIKLANI_TEAM, 'fyziklani_game_setup', 'event']
        );
        parent::tearDown();
    }

    protected function createEvent(array $data): ModelEvent
    {
        if (!isset($data['event_type_id'])) {
            $data['event_type_id'] = 1;
        }
        if (!isset($data['year'])) {
            $data['year'] = 1;
        }
        if (!isset($data['event_year'])) {
            $data['event_year'] = 10;
        }
        if (!isset($data['name'])) {
            $data['name'] = 'Dummy event';
        }
        if (!isset($data['begin'])) {
            $data['begin'] = '2016-01-01';
        }
        if (!isset($data['end'])) {
            $data['end'] = '2016-01-01';
        }
        $event = $this->getContainer()->getByType(ServiceEvent::class)->createNewModel($data);
        $this->getContainer()->getByType(ServiceFyziklaniGameSetup::class)->createNewModel([
            'event_id' => $event->event_id,
            'game_start' => new \DateTime('2016-01-01T10:00:00'),
            'game_end' => new \DateTime('2016-01-01T10:00:00'),
            'result_display' => new \DateTime('2016-01-01T10:00:00'),
            'result_hide' => new \DateTime('2016-01-01T10:00:00'),
            'refresh_delay' => 30000,
            'result_hard_display' => 1,
            'tasks_on_board' => 7,
            'available_points' => '5,3,2,1',
        ]);
        return $event;
    }

    protected function createTeam(array $data): ModelFyziklaniTeam
    {
        if (!isset($data['event_id'])) {
            $data['event_id'] = $this->event->event_id;
        }
        if (!isset($data['name'])) {
            $data['name'] = 'Dummy tým';
        }
        if (!isset($data['status'])) {
            $data['status'] = 'applied';
        }
        if (!isset($data['category'])) {
            $data['category'] = 'A';
        }
        if (!isset($data['room'])) {
            $data['room'] = '101';
        }
        return $this->getContainer()->getByType(ServiceFyziklaniTeam::class)->createNewModel($data);
    }

    protected function createTask(array $data): ModelFyziklaniTask
    {
        if (!isset($data['event_id'])) {
            $data['event_id'] = $this->event->event_id;
        }
        if (!isset($data['name'])) {
            $data['name'] = 'Dummy úloha';
        }
        return $this->getContainer()->getByType(ServiceFyziklaniTask::class)->createNewModel($data);
    }

    protected function createSubmit(array $data): ModelFyziklaniSubmit
    {
        return $this->getContainer()->getByType(ServiceFyziklaniSubmit::class)->createNewModel($data);
    }
}
