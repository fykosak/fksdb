<?php

declare(strict_types=1);

namespace FKSDB\Tests\Events;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\Application\Request;
use Nette\Schema\Helpers;

abstract class EventTestCase extends DatabaseTestCase
{
    protected function createEvent(array $data): EventModel
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
        if (!isset($data['registration_begin'])) {
            $data['registration_begin'] = '2016-01-01';
        }
        if (!isset($data['registration_end'])) {
            $data['registration_end'] = '2017-01-01';
        }
        return $this->container->getByType(EventService::class)->storeModel($data);
    }

    protected function createPostRequest(array $formData, array $params = []): Request
    {
        return new Request(
            'Public:Application',
            'POST',
            Helpers::merge($params, [
                'action' => 'default',
                'lang' => 'cs',
                'contestId' => '1',
                'year' => '1',
                'eventId' => $this->getEvent()->event_id,
            ]),
            Helpers::merge($formData, [
                '_do' => 'application-form-form-submit',
            ])
        );
    }

    abstract protected function getEvent(): EventModel;
}
