<?php

namespace FKSDB\Tests\PresentersTests\OrgModule;

$container = require '../../bootstrap.php';

use FKSDB\Components\Controls\Entity\Event\EventForm;
use FKSDB\ORM\DbNames;
use FKSDB\Tests\PresentersTests\EntityPresenterTestCase;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Nette\Utils\Html;
use Tester\Assert;

class EventPresenterTest extends EntityPresenterTestCase {

    /** @var int */
    private $eventId;

    protected function setUp() {
        parent::setUp();
        $this->loginUser();
        $this->insert(DbNames::TAB_ORG, ['person_id' => $this->cartesianPersonId, 'contest_id' => 1, 'since' => 1, 'order' => 1]);

        $this->eventId = $this->insert(DbNames::TAB_EVENT, [
            'event_type_id' => 1,
            'year' => 1,
            'event_year' => 1,
            'begin' => new \DateTime(),
            'end' => new \DateTime(),
            'name' => 'Dummy Event',
        ]);
    }

    public function testList() {
        $request = $this->createGetRequest('list', []);
        $response = $this->fixture->run($request);
        $html = $this->assertPageDisplay($response);
        Assert::contains('Dummy Event', $html);
        Assert::contains('FYKOSí Fyziklání', $html);
        Assert::contains('#' . $this->eventId, $html);
    }

    public function testCreate() {
        $init = $this->countEvents();
        $response = $this->createFormRequest('create', [
            'event_type_id' => 2,
            'year' => 1,
            'event_year' => 1,
            'begin' => (new \DateTime())->format('c'),
            'end' => (new \DateTime())->format('c'),
            'name' => 'Dummy Event',
        ]);
        Assert::type(RedirectResponse::class, $response);
        $after = $this->countEvents();
        Assert::equal($init + 1, $after);
    }

    public function testCreateDuplicate() {
        $init = $this->countEvents();
        $response = $this->createFormRequest('create', [
            'event_type_id' => 1,
            'year' => 1,
            'event_year' => 1,
            'begin' => (new \DateTime())->format('c'),
            'end' => (new \DateTime())->format('c'),
            'name' => 'Dummy Event',
        ]);

        $html = $this->assertPageDisplay($response);
        Assert::contains('Error', $html);
        $after = $this->countEvents();
        Assert::equal($init, $after);
    }

    public function testEdit() {
        $response = $this->createFormRequest('edit', [
            'event_type_id' => 1,
            'year' => 1,
            'event_year' => 1,
            'begin' => (new \DateTime())->format('c'),
            'end' => (new \DateTime())->format('c'),
            'name' => 'Dummy Event edited',
        ], [
            'id' => $this->eventId,
        ]);
        if ($response instanceof TextResponse) {
            file_put_contents('t.html', (string)$response->getSource());
        }
        Assert::type(RedirectResponse::class, $response);
        $org = $this->connection->query('SELECT * FROM event where event_id=?', $this->eventId)->fetch();
        Assert::equal('Dummy Event edited', $org->name);
    }


    protected function getPresenterName(): string {
        return 'Org:Event';
    }

    protected function createPostRequest(string $action, array $params, array $postData = []): Request {
        $params['year'] = 1;
        $params['contestId'] = 1;
        return parent::createPostRequest($action, $params, $postData);
    }

    protected function createGetRequest(string $action, array $params, array $postData = []): Request {
        $params['year'] = 1;
        $params['contestId'] = 1;
        return parent::createGetRequest($action, $params, $postData);
    }

    protected function tearDown() {
        $this->connection->query('DELETE FROM event');
        parent::tearDown();
    }

    private function countEvents(): int {
        return $this->connection->query('SELECT * FROM event')->getRowCount();
    }

    protected function getContainerName(): string {
        return EventForm::CONT_EVENT;
    }
}

$testCase = new EventPresenterTest($container);
$testCase->run();
//        if ($response instanceof TextResponse) {
//            file_put_contents('t.html', (string)$response->getSource());
//        }
