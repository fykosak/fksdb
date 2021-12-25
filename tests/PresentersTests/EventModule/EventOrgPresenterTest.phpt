<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\EventModule;

$container = require '../../Bootstrap.php';

use DateTime;
use FKSDB\Components\EntityForms\EventOrgFormComponent;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Tests\PresentersTests\EntityPresenterTestCase;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Tester\Assert;

/**
 * Class EventOrgPresenterTest
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EventOrgPresenterTest extends EntityPresenterTestCase
{

    private int $personId;

    private int $eventOrgPersonId;

    private int $eventOrgId;

    private int $eventId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginUser();
        $this->insert(
            DbNames::TAB_ORG,
            ['person_id' => $this->cartesianPersonId, 'contest_id' => 1, 'since' => 1, 'order' => 1]
        );
        $this->eventId = $this->insert(DbNames::TAB_EVENT, [
            'event_type_id' => 1,
            'year' => 1,
            'event_year' => 1,
            'begin' => new DateTime(),
            'end' => new DateTime(),
            'name' => 'Dummy Event',
        ]);
        $this->eventOrgPersonId = $this->createPerson('Tester_L', 'Testrovič_L');
        $this->eventOrgId = $this->insert(
            DbNames::TAB_EVENT_ORG,
            ['event_id' => $this->eventId, 'person_id' => $this->eventOrgPersonId, 'note' => 'note-original']
        );
        $this->personId = $this->createPerson('Tester_C', 'Testrovič_C');
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
                'person_id' => (string)$this->personId,
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
                'person_id__meta' => (string)$this->eventOrgPersonId,
                'note' => 'note-edited',
            ],
        ], [
            'id' => (string)$this->eventOrgId,
        ]);
        Assert::type(RedirectResponse::class, $response);
        $org = $this->explorer->query('SELECT * FROM event_org where e_org_id=?', $this->eventOrgId)->fetch();
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
        $params['eventId'] = $this->eventId;
        return parent::createPostRequest($action, $params, $postData);
    }

    protected function createGetRequest(string $action, array $params, array $postData = []): Request
    {
        $params['eventId'] = $this->eventId;
        return parent::createGetRequest($action, $params, $postData);
    }

    protected function tearDown(): void
    {
        $this->truncateTables([DbNames::TAB_EVENT_ORG, DbNames::TAB_EVENT]);
        parent::tearDown();
    }

    private function countEventOrgs(): int
    {
        return $this->explorer->query('SELECT * FROM event_org where person_id=?', $this->personId)->getRowCount();
    }
}

$testCase = new EventOrgPresenterTest($container);
$testCase->run();
