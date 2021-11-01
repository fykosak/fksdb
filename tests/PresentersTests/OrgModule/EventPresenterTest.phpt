<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\OrgModule;

$container = require '../../Bootstrap.php';

use DateTime;
use FKSDB\Components\EntityForms\EventFormComponent;
use FKSDB\Models\ORM\DbNames;
use Nette\Application\Responses\RedirectResponse;
use Tester\Assert;

/**
 * Class EventPresenterTest
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EventPresenterTest extends AbstractOrgPresenterTestCase
{

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
    }

    public function testList(): void
    {
        $request = $this->createGetRequest('list', []);
        $response = $this->fixture->run($request);
        $html = $this->assertPageDisplay($response);
        Assert::contains('Dummy Event', $html);
        Assert::contains('FYKOSí Fyziklání', $html);
        Assert::contains('#' . $this->eventId, $html);
    }

    public function testCreate(): void
    {
        $init = $this->countEvents();
        $response = $this->createFormRequest('create', [
            EventFormComponent::CONT_EVENT => [
                'event_type_id' => (string)2,
                'year' => (string)1,
                'event_year' => (string)1,
                'begin' => (new DateTime())->format('c'),
                'end' => (new DateTime())->format('c'),
                'name' => 'Dummy Event',
            ],
        ]);
        Assert::type(RedirectResponse::class, $response);
        $after = $this->countEvents();
        Assert::equal($init + 1, $after);
    }

    public function testCreateDuplicate(): void
    {
        $init = $this->countEvents();
        $response = $this->createFormRequest('create', [
            EventFormComponent::CONT_EVENT => [
                'event_type_id' => (string)1,
                'year' => (string)1,
                'event_year' => (string)1,
                'begin' => (new DateTime())->format('c'),
                'end' => (new DateTime())->format('c'),
                'name' => 'Dummy Event',
            ],
        ]);

        $html = $this->assertPageDisplay($response);
        Assert::contains('SQLSTATE[23000]:', $html);
        $after = $this->countEvents();
        Assert::equal($init, $after);
    }

    public function testEdit(): void
    {
        $response = $this->createFormRequest('edit', [
            EventFormComponent::CONT_EVENT => [
                'event_type_id' => (string)1,
                'year' => (string)1,
                'event_year' => (string)1,
                'begin' => (new DateTime())->format('c'),
                'end' => (new DateTime())->format('c'),
                'name' => 'Dummy Event edited',
            ],
        ], [
            'id' => $this->eventId,
        ]);
        Assert::type(RedirectResponse::class, $response);
        $org = $this->explorer->query('SELECT * FROM event where event_id=?', $this->eventId)->fetch();
        Assert::equal('Dummy Event edited', $org->name);
    }

    protected function getPresenterName(): string
    {
        return 'Org:Event';
    }

    protected function tearDown(): void
    {
        $this->truncateTables([DbNames::TAB_EVENT]);
        parent::tearDown();
    }

    private function countEvents(): int
    {
        return $this->explorer->query('SELECT * FROM event')->getRowCount();
    }
}

$testCase = new EventPresenterTest($container);
$testCase->run();
//        if ($response instanceof TextResponse) {
//            file_put_contents('t.html', (string)$response->getSource());
//        }
