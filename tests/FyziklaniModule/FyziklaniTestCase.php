<?php

namespace FKSDB\Tests\FyziklaniModule;

use FKSDB\Tests\DatabaseTestCase;
use FKSDB\ORM\DbNames;
use MockEnvironment\MockApplicationTrait;
use Nette\Database\Row;
use Nette\DI\Container;
use Nette\Utils\DateTime;

abstract class FyziklaniTestCase extends DatabaseTestCase {

    use MockApplicationTrait;

    /** @var int */
    protected $eventId;
    /** @var int */
    protected $userPersonId;

    /**
     * FyziklaniTestCase constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->setContainer($container);
    }

    protected function setUp() {
        parent::setUp();
        $this->connection->query("INSERT INTO event_type (event_type_id, contest_id, name) VALUES (1, 1, 'Fyziklání')");
        $this->connection->query("INSERT INTO event_status (status) VALUES
            ('pending'),
            ('spare'),
            ('approved'),
            ('participated'),
            ('missed'),
            ('cancelled'),
            ('invited'),
            ('applied'),
            ('applied.tsaf'),
            ('applied.notsaf')");

        $this->userPersonId = $this->createPerson('Paní', 'Černá', ['email' => 'cerna@hrad.cz', 'born' => DateTime::from('2000-01-01')], true);
        $this->insert(DbNames::TAB_ORG, ['person_id' => $this->userPersonId, 'contest_id' => 1, 'since' => 0, 'order' => 0]);
    }

    protected function tearDown() {
        $this->connection->query('DELETE FROM fyziklani_submit');
        $this->connection->query('DELETE FROM fyziklani_task');
        $this->connection->query('DELETE FROM e_fyziklani_team');
        $this->connection->query('DELETE FROM event_status');
        $this->connection->query('DELETE FROM fyziklani_game_setup');
        $this->connection->query('DELETE FROM event');
        $this->connection->query('DELETE FROM event_type');

        parent::tearDown();
    }

    protected function createEvent(array $data): int {
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
        $eventId = $this->insert(DbNames::TAB_EVENT, $data);
        $this->insert(DbNames::TAB_FYZIKLANI_GAME_SETUP, [
            'event_id' => $eventId,
            'game_start' => new DateTime('2016-01-01T10:00:00'),
            'game_end' => new DateTime('2016-01-01T10:00:00'),
            'result_display' => new DateTime('2016-01-01T10:00:00'),
            'result_hide' => new DateTime('2016-01-01T10:00:00'),
            'refresh_delay' => 30000,
            'result_hard_display' => 1,
            'tasks_on_board' => 7,
            'available_points' => '5,3,2,1',
        ]);
        return $eventId;
    }

    protected function createTeam(array $data): int {
        if (!isset($data['event_id'])) {
            $data['event_id'] = $this->eventId;
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
        return $this->insert(DbNames::TAB_E_FYZIKLANI_TEAM, $data);
    }

    protected function createTask(array $data): int {
        if (!isset($data['event_id'])) {
            $data['event_id'] = $this->eventId;
        }
        if (!isset($data['name'])) {
            $data['name'] = 'Dummy úloha';
        }
        return $this->insert(DbNames::TAB_FYZIKLANI_TASK, $data);
    }

    protected function createSubmit(array $data): int {
        return $this->insert(DbNames::TAB_FYZIKLANI_SUBMIT, $data);

    }

    protected function findSubmit(int $taskId, int $teamId): Row {
        return $this->connection->fetch(
            'SELECT * FROM fyziklani_submit WHERE fyziklani_task_id = ? AND e_fyziklani_team_id = ?', $taskId, $teamId);
    }

    protected function findTeam(int $teamId): Row {
        return $this->connection->fetch(
            'SELECT * FROM e_fyziklani_team WHERE e_fyziklani_team_id = ?', $teamId);
    }
}
