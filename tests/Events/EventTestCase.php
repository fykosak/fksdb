<?php

declare(strict_types=1);

namespace FKSDB\Tests\Events;

use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use FKSDB\Models\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\Models\ORM\Services\ServiceEvent;
use FKSDB\Models\ORM\Services\ServiceEventParticipant;
use FKSDB\Models\ORM\Services\ServicePerson;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\Application\Request;
use Nette\Database\Row;
use Nette\Schema\Helpers;
use Tester\Assert;

abstract class EventTestCase extends DatabaseTestCase
{
    protected function createEvent(array $data): ModelEvent
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
        return $this->getContainer()->getByType(ServiceEvent::class)->createNewModel($data);
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
                'eventId' => $this->getEvent()->event_id,
            ]),
            Helpers::merge($formData, [
                '_do' => 'application-form-form-submit',
            ])
        );
    }

    abstract protected function getEvent(): ModelEvent;

    protected function assertApplication(ModelEvent $event, string $email): ModelEventParticipant
    {
        $person = $this->getContainer()->getByType(ServicePerson::class)->findByEmail($email);
        Assert::notEqual(null, $person);
        $application = $this->getContainer()->getByType(ServiceEventParticipant::class)->getTable()->where([
            'event_id' => $event->event_id,
            'person_id' => $person->person_id,
        ])->fetch();
        Assert::notEqual(null, $application);
        return $application;
    }

    protected function assertExtendedApplication(ModelEventParticipant $application, string $table): Row
    {
        $application = $this->explorer->fetch(
            'SELECT * FROM `' . $table . '` WHERE event_participant_id = ?',
            $application->event_participant_id
        );
        Assert::notEqual(null, $application);
        return $application;
    }

//    protected function assertApplicationSchedule(ModelEventParticipant $application, string $table): Row
//    {
//        $application = $this->explorer->fetch(
//            'SELECT * FROM `' . $table . '` WHERE person_id = ?',
//            $application->person_id
//        );
//        Assert::notEqual(null, $application);
//        return $application;
//    }
}
