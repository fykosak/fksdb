<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\EventModule;

// phpcs:disable
$container = require '../../Bootstrap.php';

// phpcs:enable
use FKSDB\Components\EntityForms\EventOrganizerFormComponent;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventOrganizerModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\EventOrganizerService;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\ORM\Services\OrganizerService;
use FKSDB\Tests\PresentersTests\EntityPresenterTestCase;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Tester\Assert;

class EventOrganizerPresenterTest extends EntityPresenterTestCase
{
    private PersonModel $person;
    private PersonModel $eventOrganizerPerson;
    private EventOrganizerModel $eventOrganizer;
    private EventModel $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginUser();
        $this->container->getByType(OrganizerService::class)->storeModel(
            ['person_id' => $this->cartesianPerson->person_id, 'contest_id' => 1, 'since' => 1, 'order' => 1]
        );
        $this->event = $this->container->getByType(EventService::class)->storeModel([
            'event_type_id' => 1,
            'year' => 1,
            'event_year' => 1,
            'begin' => new \DateTime(),
            'end' => new \DateTime(),
            'name' => 'Dummy Event',
            'registration_begin' => new \DateTime(),
            'registration_end' => new \DateTime(),
        ]);
        $this->eventOrganizerPerson = $this->createPerson('Tester_L', 'Testrovič_L');
        $this->eventOrganizer = $this->container->getByType(EventOrganizerService::class)->storeModel([
            'event_id' => $this->event->event_id,
            'person_id' => $this->eventOrganizerPerson->person_id,
            'note' => 'note-original',
        ]);
        $this->person = $this->createPerson('Tester_C', 'Testrovič_C');
    }

    public function testList(): void
    {
        $request = $this->createGetRequest('list', []);
        $response = $this->fixture->run($request);
        $html = $this->assertPageDisplay($response);
        Assert::contains('Tester_L', $html);
        Assert::contains('Testrovič_L', $html);
        Assert::contains('note-original', $html);
    }

    public function testCreate(): void
    {
        $init = $this->countEventOrganizers();
        $response = $this->createFormRequest('create', [
            EventOrganizerFormComponent::CONTAINER => [
                'person_id' => (string)$this->person->person_id,
                'person_id_container' => self::personToValues($this->event->event_type->contest, $this->person),
                'note' => 'note-c',
            ],
        ]);
        Assert::type(RedirectResponse::class, $response);
        $after = $this->countEventOrganizers();
        Assert::equal($init + 1, $after);
    }

    public function testModelErrorCreate(): void
    {
        $init = $this->countEventOrganizers();
        $response = $this->createFormRequest('create', [
            EventOrganizerFormComponent::CONTAINER => [
                'person_id' => null, // empty personId
                'note' => '',
            ],
        ]);
        $html = $this->assertPageDisplay($response);
        Assert::contains('alert-danger', $html);
        $after = $this->countEventOrganizers();
        Assert::equal($init, $after);
    }

    public function testEdit(): void
    {
        $response = $this->createFormRequest('edit', [
            EventOrganizerFormComponent::CONTAINER => [
                'person_id' => (string)$this->eventOrganizerPerson->person_id,
                'person_id_container' => self::personToValues(
                    $this->event->event_type->contest,
                    $this->eventOrganizerPerson
                ),
                'note' => 'note-edited',
            ],
        ], [
            'id' => (string)$this->eventOrganizer->e_org_id,
        ]);
        Assert::type(RedirectResponse::class, $response);
        /** @var EventOrganizerModel $organizer */
        $organizer = $this->container
            ->getByType(EventOrganizerService::class)->findByPrimary($this->eventOrganizer->e_org_id);
        Assert::equal('note-edited', $organizer->note);
    }

    public function testDetail(): void
    {
        $request = $this->createGetRequest('list', []);
        $response = $this->fixture->run($request);
        $html = $this->assertPageDisplay($response);
        Assert::contains('Tester_L', $html);
        Assert::contains('Testrovič_L', $html);
        Assert::contains('note-original', $html);
    }

    protected function getPresenterName(): string
    {
        return 'Event:EventOrganizer';
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

    private function countEventOrganizers(): int
    {
        return $this->container
            ->getByType(EventOrganizerService::class)
            ->getTable()
            ->where(['person_id' => $this->person->person_id])
            ->count('*');
    }
}

// phpcs:disable
$testCase = new EventOrganizerPresenterTest($container);
$testCase->run();
// phpcs:enable
