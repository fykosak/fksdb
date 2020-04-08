<?php

namespace FyziklaniModule;

$container = require '../bootstrap.php';

use FKSDB\ORM\DbNames;
use MockEnvironment\MockApplicationTrait;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Nette\DI\Container;
use Nette\Utils\DateTime;
use Tester\Assert;

class AuthorizationTest extends FyziklaniTestCase {

    use MockApplicationTrait;

    private $perPerson;

    private $perOrg;

    private $perOrgOther;

    private $perContestant;

    private $submitId;

    function __construct(Container $container) {
        parent::__construct($container);
        $this->setContainer($container);
    }

    protected function setUp() {
        parent::setUp();

        $this->perPerson = $this->createPerson('Karkulka', 'Červená', [
            'email' => 'karkulka@les.cz', 'born' => DateTime::from('2000-01-01')
        ], true);

        $this->perOrg = $this->createPerson('Karkulka', 'Červená', [
            'email' => 'karkulka2@les.cz', 'born' => DateTime::from('2000-01-01')
        ], true);
        $this->insert(DbNames::TAB_ORG, ['person_id' => $this->perOrg, 'contest_id' => 1, 'since' => 0, 'order' => 0]);

        $this->perOrgOther = $this->createPerson('Karkulka', 'Červená', [
            'email' => 'karkulka3@les.cz', 'born' => DateTime::from('2000-01-01')
        ], true);
        $this->insert(DbNames::TAB_ORG, ['person_id' => $this->perOrgOther, 'contest_id' => 2, 'since' => 0, 'order' => 0]);

        $this->perContestant = $this->createPerson('Karkulka', 'Červená', [
            'email' => 'karkulka4@les.cz', 'born' => DateTime::from('2000-01-01')
        ], true);
        $this->insert(DbNames::TAB_CONTESTANT_BASE, ['person_id' => $this->perContestant, 'contest_id' => 1, 'year' => 1]);

        $this->eventId = $this->createEvent([]);
        $this->connection->query('INSERT INTO fyziklani_task', [
            'event_id' => $this->eventId,
            'label' => 'AA',
            'name' => 'tmp',
        ]);
        $taskId = $this->connection->getInsertId();

        $this->connection->query('INSERT INTO e_fyziklani_team', [
            'event_id' => $this->eventId,
            'name' => 'bar',
            'status' => 'applied',
            'category' => 'C',
        ]);
        $teamId = $this->connection->getInsertId();
        $this->connection->query('INSERT INTO fyziklani_submit', [
            'fyziklani_task_id' => $taskId,
            'e_fyziklani_team_id' => $teamId,
            'points' => 5,
        ]);
        $this->submitId = $this->connection->getInsertId();

        $this->mockApplication();
    }

    protected function tearDown() {
        $this->connection->query("DELETE FROM contestant_base");

        parent::tearDown();
    }

    public function getTestData() {
        return [
            [null, 'Fyziklani:Submit', ['entry', 'edit', 'list'], false],
            ['perPerson', 'Fyziklani:Submit', ['entry', 'edit', 'list'], false],
            ['perOrg', 'Fyziklani:Submit', ['entry', 'list'], true], # TODO 'edit',
            ['perOrgOther', 'Fyziklani:Submit', ['entry', 'edit', 'list'], false],
            ['perContestant', 'Fyziklani:Submit', ['entry', 'edit', 'list'], false],
        ];
    }

    private function createGetRequest($presenterName, $action) {
        $params = [
            'lang' => 'cs',
            'contestId' => 1,
            'year' => 1,
            'eventId' => $this->eventId,
            'action' => $action,
            'id' => $this->submitId,
        ];

        return new Request($presenterName, 'GET', $params);
    }

    /**
     * @dataProvider getTestData
     */
    public function testAccess($personId, $presenterName, $actions, $results) {
        if (!is_array($actions)) {
            $actions = [$actions];
        }
        if (!is_array($results)) {
            $results = array_fill(0, count($actions), $results);
        }
        if ($personId) {
            /* Use indirect access because data provider is called before test set up. */
            $this->authenticate($this->{$personId});
        }

        $presenter = $this->createPresenter($presenterName);
        foreach ($actions as $i => $action) {
            $request = $this->createGetRequest($presenterName, $action);
            $forbidden = false;
            $response = null;
            try {
                $response = $presenter->run($request);
            } catch (ForbiddenRequestException $e) {
                $forbidden = true;
                $response = $e->getCode();
            } catch (BadRequestException $e) {
                $forbidden = ($e->getCode() == 403);
                $response = $e->getCode();
            }
            if ($results[$i]) {
                if (is_object($response)) {
                    Assert::type(TextResponse::class, $response);
                } else {
                    Assert::notSame(403, $response);
                }
            } else {
                if (!$forbidden) {
                    Assert::type(RedirectResponse::class, $response);
                    $url = $response->getUrl();
                    Assert::contains('login', $url);
                }
            }
        }
    }

}

$testCase = new AuthorizationTest($container);
$testCase->run();
