<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\EventModule;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\StudyYear;
use FKSDB\Models\ORM\Services\ContestYearService;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\ORM\Services\SchoolService;
use FKSDB\Tests\PresentersTests\EntityPresenterTestCase;
use Nette\Application\Request;

abstract class TeamApplicationPresenterTestCase extends EntityPresenterTestCase
{
    protected PersonModel $personA;
    protected PersonModel $personB;
    protected PersonModel $personC;
    protected PersonModel $personD;
    protected PersonModel $personE;

    protected EventModel $event;

    protected function setUp(): void
    {
        parent::setUp();
        $school = $this->container->getByType(SchoolService::class)->getTable()->fetch();
        $this->mockApplication();

        $this->personA = $this->createPerson('A', 'A', ['email' => 'a@a.a'], ['login' => 'AAAAAA', 'hash' => 'AAAAAA']);
        $this->createPersonHistory(
            $this->personA,
            ContestYearService::getCurrentAcademicYear(),
            $school,
            StudyYear::High1,
            '1A'
        );

        $this->personB = $this->createPerson('B', 'B', ['email' => 'b@b.b'], ['login' => 'BBBBBB', 'hash' => 'BBBBBB']);
        $this->createPersonHistory(
            $this->personB,
            ContestYearService::getCurrentAcademicYear(),
            $school,
            StudyYear::High2,
            '2A'
        );

        $this->personC = $this->createPerson('C', 'C', ['email' => 'c@c.c'], ['login' => 'CCCCCC', 'hash' => 'CCCCCC']);
        $this->createPersonHistory(
            $this->personC,
            ContestYearService::getCurrentAcademicYear(),
            $school,
            StudyYear::High3,
            '3C'
        );

        $this->personD = $this->createPerson('D', 'D', ['email' => 'd@d.d'], ['login' => 'DDDDDD', 'hash' => 'DDDDDD']);
        $this->createPersonHistory(
            $this->personD,
            ContestYearService::getCurrentAcademicYear(),
            $school,
            StudyYear::High4,
            '4D'
        );

        $this->personE = $this->createPerson('E', 'E', ['email' => 'e@e.e'], ['login' => 'EEEEEE', 'hash' => 'EEEEEE']);
        $this->createPersonHistory(
            $this->personE,
            ContestYearService::getCurrentAcademicYear(),
            $school,
            StudyYear::Primary9,
            '9D'
        );

        $this->event = $this->createEvent();
    }

    abstract protected function createEvent(): EventModel;

    public function outDateEvent(): void
    {
        $this->container->getByType(EventService::class)->storeModel([
            'registration_begin' => (new \DateTime())->sub(new \DateInterval('P2D')),
            'registration_end' => (new \DateTime())->sub(new \DateInterval('P1D')),
        ], $this->event);
    }

    protected function createPostRequest(string $action, array $params, array $postData = []): Request
    {
        $params['eventId'] = $this->event->event_id;
        return parent::createPostRequest($action, $params, $postData);
    }

    protected function createGetRequest(string $action, array $params, array $postData = []): Request
    {
        $params['eventId'] = $this->event->event_id;
        return parent::createGetRequest($action, $params, $postData);
    }

    protected function getPresenterName(): string
    {
        return 'Event:TeamApplication';
    }
}
