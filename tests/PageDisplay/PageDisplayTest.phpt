<?php

namespace PageDisplay;

$container = require '../bootstrap.php';

use FKSDB\Modules\CommonModule\PersonPresenter;
use FKSDB\ORM\DbNames;
use MockEnvironment\MockApplicationTrait;
use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\ITemplate;
use Nette\DI\Container;
use Tester\Assert;

/**
 * Class PageDisplayTest
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PageDisplayTest extends \DatabaseTestCase {
    use MockApplicationTrait;

    /** @var int */
    protected $personId;

    /** @var PersonPresenter */
    protected $fixture;

    /**
     * PageDisplayTest constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->setContainer($container);
    }

    protected function setUp() {
        parent::setUp();

        $this->personId = $this->insert(DbNames::TAB_PERSON, [
            'family_name' => 'Cartesian',
            'other_name' => 'Cartesiansky',
            'gender' => 'M',
        ]);
        $this->insert(DbNames::TAB_PERSON_INFO, ['person_id' => $this->personId]);

        $loginId = $this->insert(DbNames::TAB_LOGIN, ['person_id' => $this->personId, 'active' => 1]);
        $this->insert(DbNames::TAB_ORG, ['person_id' => $this->personId, 'contest_id' => 1, 'since' => 1, 'order' => 1]);
        $this->insert(DbNames::TAB_GRANT, ['login_id' => $loginId, 'role_id' => 1000, 'contest_id' => 1]);
        $this->authenticate($loginId);

        $this->fixture = $this->createPresenter('Core:Dispatch');
    }

    final protected function createRequest(string $presenterName, array $params): Request {
        $params['lang'] = $params['lang'] ?? 'en';
        $params['action'] = $params['action'] ?? 'default';
        return new Request($presenterName, 'GET', $params);
    }

    /**
     * @dataProvider getPages
     */
    public function testDisplay(string $presenterName, array $params) {
        $this->fixture = $this->createPresenter($presenterName);
        $request = $this->createRequest($presenterName, $params);
        $response = $this->fixture->run($request);
        Assert::type(TextResponse::class, $response);
        $source = $response->getSource();
        Assert::type(ITemplate::class, $source);

        Assert::noError(function () use ($source) {
            return (string)$source;
        });

    }

    public function getPages(): array {
        return [
            ['Common:Chart', ['action' => 'list']],
            ['Common:Dashboard', []],
            ['Common:Deduplicate', ['action' => 'person']],
            ['Common:Person', ['action' => 'create']],
            //    ['Common:Person', ['action' => 'edit', 'id' => $this->personId]],
            //    ['Common:Person', ['action' => 'detail', 'id' => $this->personId]],
            ['Common:Person', ['action' => 'pizza']],
            ['Common:Person', ['action' => 'search']],
            ['Common:School', ['action' => 'list']],
            ['Common:School', ['action' => 'create']],
            ['Common:Spam', ['action' => 'list']],
            ['Common:Validation', []],
            ['Common:Validation', ['action' => 'list']],
            ['Common:Validation', ['action' => 'preview']],

            ['Org:Contestant', ['contestId' => 1, 'year' => 1, 'action' => 'create']],
            ['Org:Contestant', ['contestId' => 1, 'year' => 1, 'action' => 'list']],
            ['Org:Dashboard', ['contestId' => 1, 'year' => 1]],
            ['Org:Event', ['contestId' => 1, 'year' => 1, 'action' => 'create']],
            ['Org:Event', ['contestId' => 1, 'year' => 1, 'action' => 'list']],
            ['Org:Export', ['contestId' => 1, 'year' => 1, 'action' => 'compose']],
            ['Org:Export', ['contestId' => 1, 'year' => 1, 'action' => 'list']],
            ['Org:Inbox', ['contestId' => 1, 'year' => 1, 'action' => 'corrected', 'series' => 1]],
            ['Org:Inbox', ['contestId' => 1, 'year' => 1, 'action' => 'default', 'series' => 1]],
            ['Org:Inbox', ['contestId' => 1, 'year' => 1, 'action' => 'handout', 'series' => 1]],
            ['Org:Inbox', ['contestId' => 1, 'year' => 1, 'action' => 'inbox', 'series' => 1]],
            ['Org:Inbox', ['contestId' => 1, 'year' => 1, 'action' => 'list', 'series' => 1]],
            ['Org:Org', ['contestId' => 1, 'year' => 1, 'action' => 'list']],
            ['Org:Org', ['contestId' => 1, 'year' => 1, 'action' => 'create']],
            ['Org:Points', ['contestId' => 1, 'year' => 1, 'action' => 'entry', 'series' => 1]],
            ['Org:Points', ['contestId' => 1, 'year' => 1, 'action' => 'preview', 'series' => 1]],
            // ['Org:Tasks', ['contestId' => 1, 'year' => 1, 'action' => 'import']], TODO FIX astrid on travis
            ['Org:Teacher', ['contestId' => 1, 'year' => 1, 'action' => 'list']],
            ['Org:Teacher', ['contestId' => 1, 'year' => 1, 'action' => 'create']],

            //    ['Event:Dashboard', ['eventId' => 1]],
        ];
    }
}

$testCase = new PageDisplayTest($container);
$testCase->run();
