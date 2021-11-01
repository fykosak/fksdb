<?php

declare(strict_types=1);

namespace FKSDB\Tests\Events;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Tests\MockEnvironment\MockApplicationTrait;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\Application\Request;
use Nette\Database\Row;
use Nette\DI\Container;
use Nette\Schema\Helpers;
use Tester\Assert;

abstract class EventTestCase extends DatabaseTestCase
{
    use MockApplicationTrait;

    /**
     * EventTestCase constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->setContainer($container);
    }

    protected function tearDown(): void
    {
        $this->truncateTables([DbNames::TAB_EVENT_PARTICIPANT, DbNames::TAB_EVENT, DbNames::TAB_AUTH_TOKEN]);
        parent::tearDown();
    }

    protected function createEvent(array $data): int
    {
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

    protected function createPostRequest(array $formData, array $params = []): Request
    {
        return new Request(
            'Public:Application',
            'POST',
            Helpers::merge($params, [
                'action' => 'default',
                'lang' => 'cs',
                'contestId' => (string)1,
                'year' => (string)1,
                'eventId' => $this->getEventId(),
            ]),
            Helpers::merge($formData, [
                '_do' => 'application-form-form-submit',
            ])
        );
    }

    abstract protected function getEventId(): int;

    protected function assertApplication(int $eventId, string $email): Row
    {
        $personId = $this->explorer->fetchField('SELECT person_id FROM person_info WHERE email=?', $email);

        Assert::notEqual(null, $personId);

        $application = $this->explorer->fetch(
            'SELECT * FROM event_participant WHERE event_id = ? AND person_id = ?',
            $eventId,
            $personId
        );
        Assert::notEqual(null, $application);
        return $application;
    }

    protected function assertExtendedApplication(Row $application, string $table): Row
    {
        $application = $this->explorer->fetch(
            'SELECT * FROM `' . $table . '` WHERE event_participant_id = ?',
            $application->event_participant_id
        );
        Assert::notEqual(null, $application);
        return $application;
    }
}
