<?php

namespace FyziklaniModule;

use DatabaseTestCase;
use DbNames;
use MockEnvironment\MockApplicationTrait;
use Nette\Database\Row;
use Nette\DateTime;
use Tester\Assert;

abstract class FyziklaniTestCase extends DatabaseTestCase {

    use MockApplicationTrait;

    protected $eventId;

    protected $userPersonId;

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

        $this->userPersonId = $this->createPerson('Paní', 'Černá', array('email' => 'cerna@hrad.cz', 'born' => DateTime::from('2000-01-01')), true);
        $this->insert(DbNames::TAB_ORG, array('person_id' => $this->userPersonId, 'contest_id' => 1, 'since' => 0, 'order' => 0));
    }

    protected function tearDown() {
        $this->connection->query("DELETE FROM fyziklani_submit");
        $this->connection->query("DELETE FROM fyziklani_task");
        $this->connection->query("DELETE FROM e_fyziklani_team");
        $this->connection->query("DELETE FROM event_status");
        $this->connection->query("DELETE FROM event");
        $this->connection->query("DELETE FROM event_type");

        parent::tearDown();
    }

    protected function createEvent($data) {
        if (!isset($data['event_type_id'])) {
            $data['event_type_id'] = 1;
        }
        if (!isset($data['year'])) {
            $data['year'] = 1;
        }
        if (!isset($data['event_year'])) {
            $data['event_year'] = 1;
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
        $this->connection->query('INSERT INTO event', $data);
        return $this->connection->lastInsertId();
    }

    protected function createTeam($data) {
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
        $this->connection->query('INSERT INTO e_fyziklani_team', $data);
        return $this->connection->lastInsertId();
    }

    protected function createTask($data) {
        if (!isset($data['event_id'])) {
            $data['event_id'] = $this->eventId;
        }
        if (!isset($data['name'])) {
            $data['name'] = 'Dummy úloha';
        }
        $this->connection->query('INSERT INTO fyziklani_task', $data);
        return $this->connection->lastInsertId();
    }

    protected function createSubmit($data) {
        $this->connection->query('INSERT INTO fyziklani_submit', $data);
        return $this->connection->lastInsertId();
    }

    protected function findSubmit($taskId, $teamId) {
        $submit = $this->connection->fetch(
                'SELECT * FROM fyziklani_submit WHERE fyziklani_task_id = ? AND e_fyziklani_team_id = ?', $taskId, $teamId);
        return $submit;
    }

    protected function findTeam($teamId) {
        $submit = $this->connection->fetch(
                'SELECT * FROM e_fyziklani_team WHERE e_fyziklani_team_id = ?', $teamId);
        return $submit;
    }
}
