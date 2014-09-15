<?php

namespace Events;

use DatabaseTestCase;
use MockEnvironment\MockApplicationTrait;
use Nette\Application\Request;
use Nette\Database\Row;
use Tester\Assert;

abstract class EventTestCase extends DatabaseTestCase {

    use MockApplicationTrait;

    protected $eventId;

    protected function setUp() {
        parent::setUp();
        $this->connection->query("INSERT INTO event_type (event_type_id, contest_id, name) VALUES (1, 1, 'Fyziklání'), (2, 1, 'DSEF'), (7, 1, 'TSAF')");
        $this->connection->query("INSERT INTO event_status (status) VALUES 
            ('pending'),
            ('spare'), 
            ('approved'), 
            ('participated'),             
            ('missed'), 
            ('cancelled'), 
            ('applied'),
            ('applied.tsaf'),
            ('applied.notsaf')");
    }

    protected function tearDown() {
        $this->connection->query("DELETE FROM event_participant");
        $this->connection->query("DELETE FROM event_status");
        $this->connection->query("DELETE FROM event");
        $this->connection->query("DELETE FROM event_type");
        $this->connection->query("DELETE FROM auth_token");
        $this->connection->query("DELETE FROM login");

        parent::tearDown();
    }

    protected function createEvent($data) {
        if (!isset($data['year'])) {
            $data['year'] = 1;
        }
        $this->connection->query('INSERT INTO event', $data);
        return $this->connection->lastInsertId();
    }

    protected function createPostRequest($postData) {
        $request = new Request('Public:Application', 'POST', array(
            'action' => 'default',
            'lang' => 'cs',
            'contestId' => 1,
            'year' => 1,
            'eventId' => $this->eventId,
            'do' => 'application-form-form-submit',
                ), $postData);
        return $request;
    }

    protected function assertApplication($eventId, $email) {
        $personId = $this->connection->fetchField('SELECT person_id FROM person_info WHERE email=?', $email);
        Assert::notEqual(false, $personId);

        $application = $this->connection->fetch('SELECT * FROM event_participant WHERE event_id = ? AND person_id = ?', $eventId, $personId);
        Assert::notEqual(false, $application);
        return $application;
    }

    protected function assertExtendedApplication(Row $application, $table) {
        $application = $this->connection->fetch('SELECT * FROM `' . $table . '` WHERE event_participant_id = ?', $application->event_participant_id);
        Assert::notEqual(false, $application);
        return $application;
    }

}
