<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\FyziklaniModule;

use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\Fyziklani\GameSetupService;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use FKSDB\Models\ORM\Services\Fyziklani\TaskService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\ORM\Services\Fyziklani\TeamMemberService;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\ORM\Services\OrgService;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\Utils\DateTime;

abstract class FyziklaniTestCase extends DatabaseTestCase
{
    protected EventModel $event;
    protected PersonModel $userPerson;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userPerson = $this->createPerson(
            'Paní',
            'Černá',
            ['email' => 'cerna@hrad.cz', 'born' => DateTime::from('2000-01-01')],
            []
        );
        $this->container->getByType(OrgService::class)->storeModel(
            ['person_id' => $this->userPerson->person_id, 'contest_id' => 1, 'since' => 0, 'order' => 0]
        );
    }

    protected function createEvent(array $data): EventModel
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
        $event = $this->container->getByType(EventService::class)->storeModel($data);
        $this->container->getByType(GameSetupService::class)->storeModel([
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

    protected function createTeam(array $data): TeamModel2
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
        return $this->container->getByType(TeamService2::class)->storeModel($data);
    }

    protected function createTeamMember(array $data): TeamMemberModel
    {
        if (!isset($data['name'])) {
            $data['name'] = 'Dummy';
        }
        if (!isset($data['surname'])) {
            $data['surname'] = 'Tester';
        }
        $person = $this->createPerson($data['name'], $data['surname']);
        return $this->container->getByType(TeamMemberService::class)->storeModel([
            'person_id' => $person->person_id,
            'fyziklani_team_id' => $data['fyziklani_team_id']
        ]);
    }

    protected function createTask(array $data): TaskModel
    {
        if (!isset($data['event_id'])) {
            $data['event_id'] = $this->event->event_id;
        }
        if (!isset($data['name'])) {
            $data['name'] = 'Dummy úloha';
        }
        return $this->container->getByType(TaskService::class)->storeModel($data);
    }

    protected function createSubmit(array $data): SubmitModel
    {
        return $this->container->getByType(SubmitService::class)->storeModel($data);
    }
}
