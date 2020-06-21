<?php

namespace FKSDB\Tests\Events;

use FKSDB\Tests\DatabaseTestCase;
use FKSDB\ORM\DbNames;
use MockEnvironment\MockApplicationTrait;
use Nette\Application\Request;
use Nette\DI\Container;
use Nette\DI\Config\Helpers;
use Nette\Database\Row;
use Tester\Assert;

abstract class EventTestCase extends DatabaseTestCase {

    use MockApplicationTrait;

    /**
     * EventTestCase constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->setContainer($container);
    }

    /** @var int */
    protected $eventId;

    protected function setUp() {
        parent::setUp();
        $this->connection->query("INSERT INTO event_type (event_type_id, contest_id, name) VALUES (1, 1, 'Fyziklání'), (2, 1, 'DSEF'), (7, 1, 'TSAF'), (9, 1, 'FoL')");
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
    }

    protected function tearDown() {
        $this->connection->query('DELETE FROM event_participant');
        $this->connection->query('DELETE FROM event_status');
        $this->connection->query('DELETE FROM event');
        $this->connection->query('DELETE FROM event_type');
        $this->connection->query('DELETE FROM auth_token');
        parent::tearDown();
    }

    protected function createEvent(array $data): int {
        if (!isset($data['year'])) {
            $data['year'] = 1;
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
        return $this->insert(DbNames::TAB_EVENT, $data);
    }

    protected function createPostRequest(array $postData, array $post = []): Request {
        $post = Helpers::merge($post, [
            'action' => 'default',
            'lang' => 'cs',
            'contestId' => 1,
            'year' => 1,
            'eventId' => $this->eventId,
            'do' => 'application-form-form-submit',
        ]);

        return new Request('Public:Application', 'POST', $post, $postData);
    }

    protected function assertApplication(int $eventId, string $email): Row {
        $personId = $this->connection->fetchField('SELECT person_id FROM person_info WHERE email=?', $email);
        Assert::notEqual(false, $personId);

        $application = $this->connection->fetch('SELECT * FROM event_participant WHERE event_id = ? AND person_id = ?', $eventId, $personId);
        Assert::notEqual(false, $application);
        return $application;
    }

    protected function assertExtendedApplication(Row $application, string $table): Row {
        $application = $this->connection->fetch('SELECT * FROM `' . $table . '` WHERE event_participant_id = ?', $application->event_participant_id);
        Assert::notEqual(false, $application);
        return $application;
    }

}
