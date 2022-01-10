<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\EventModule;

$container = require '../../Bootstrap.php';

use DateTime;
use FKSDB\Components\EntityForms\EventOrgFormComponent;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventOrg;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Services\ServiceEvent;
use FKSDB\Models\ORM\Services\ServiceEventOrg;
use FKSDB\Models\ORM\Services\ServiceOrg;
use FKSDB\Tests\PresentersTests\EntityPresenterTestCase;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Tester\Assert;

class EventOrgPresenterTest extends EntityPresenterTestCase
{

    private ModelPerson $person;
    private ModelPerson $eventOrgPerson;
    private ModelEventOrg $eventOrg;
    private ModelEvent $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginUser();
        $this->getContainer()->getByType(ServiceOrg::class)->createNewModel(
            ['person_id' => $this->cartesianPerson->person_id, 'contest_id' => 1, 'since' => 1, 'order' => 1]
        );
        $this->event = $this->getContainer()->getByType(ServiceEvent::class)->createNewModel([
            'event_type_id' => 1,
            'year' => 1,
            'event_year' => 1,
            'begin' => new DateTime(),
            'end' => new DateTime(),
            'name' => 'Dummy Event',
        ]);
        $this->eventOrgPerson = $this->createPerson('Tester_L', 'Testrovič_L');
        $this->eventOrg = $this->getContainer()->getByType(ServiceEventOrg::class)->createNewModel([
            'event_id' => $this->event->event_id,
            'person_id' => $this->eventOrgPerson->person_id,
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
        $init = $this->countEventOrgs();
        $response = $this->createFormRequest('create', [
            EventOrgFormComponent::CONTAINER => [
                'person_id__meta' => 'JS',
                'person_id' => (string)$this->person->person_id,
                'note' => 'note-c',
            ],
        ]);
        Assert::type(RedirectResponse::class, $response);
        $after = $this->countEventOrgs();
        Assert::equal($init + 1, $after);
    }

    public function testModelErrorCreate(): void
    {
        $init = $this->countEventOrgs();
        $response = $this->createFormRequest('create', [
            EventOrgFormComponent::CONTAINER => [
                'person_id__meta' => 'JS',
                'person_id' => null, // empty personId
                'note' => '',
            ],
        ]);
        $html = $this->assertPageDisplay($response);
        Assert::contains('Error', $html);
        $after = $this->countEventOrgs();
        Assert::equal($init, $after);
    }

    public function testEdit(): void
    {
        $response = $this->createFormRequest('edit', [
            EventOrgFormComponent::CONTAINER => [
                'person_id__meta' => (string)$this->eventOrgPerson->person_id,
                'note' => 'note-edited',
            ],
        ], [
            'id' => (string)$this->eventOrg->e_org_id,
        ]);
        Assert::type(RedirectResponse::class, $response);
        $org = $this->getContainer()
            ->getByType(ServiceEventOrg::class)
            ->getTable()
            ->where(['e_org_id' => $this->eventOrg->e_org_id])
            ->fetch();
        Assert::equal('note-edited', $org->note);
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
        return 'Event:EventOrg';
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

    private function countEventOrgs(): int
    {
        return $this->getContainer()
            ->getByType(ServiceEventOrg::class)
            ->getTable()
            ->where(['person_id' => $this->person->person_id])
            ->count('*');
    }
}

$testCase = new EventOrgPresenterTest($container);
$testCase->run();
