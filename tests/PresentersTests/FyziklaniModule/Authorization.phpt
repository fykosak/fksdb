<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\FyziklaniModule;

$container = require '../../Bootstrap.php';

use FKSDB\Models\ORM\DbNames;
use FKSDB\Tests\MockEnvironment\MockApplicationTrait;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Nette\DI\Container;
use Nette\Utils\DateTime;
use Tester\Assert;

class Authorization extends FyziklaniTestCase
{
    use MockApplicationTrait;

    private int $perPerson;
    private int $perOrg;
    private int $perOrgOther;
    private int $perContestant;
    private int $submitId;

    /**
     * AuthorizationTest constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->setContainer($container);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->perPerson = $this->createPerson('Karkulka', 'Červená', [
            'email' => 'karkulka@les.cz', 'born' => DateTime::from('2000-01-01'),
        ], []);

        $this->perOrg = $this->createPerson('Karkulka', 'Červená', [
            'email' => 'karkulka2@les.cz', 'born' => DateTime::from('2000-01-01'),
        ], []);
        $this->insert(DbNames::TAB_ORG, ['person_id' => $this->perOrg, 'contest_id' => 1, 'since' => 0, 'order' => 0]);

        $this->perOrgOther = $this->createPerson('Karkulka', 'Červená', [
            'email' => 'karkulka3@les.cz', 'born' => DateTime::from('2000-01-01'),
        ], []);
        $this->insert(
            DbNames::TAB_ORG,
            ['person_id' => $this->perOrgOther, 'contest_id' => 2, 'since' => 0, 'order' => 0]
        );

        $this->perContestant = $this->createPerson('Karkulka', 'Červená', [
            'email' => 'karkulka4@les.cz', 'born' => DateTime::from('2000-01-01'),
        ], []);
        $this->insert(
            DbNames::TAB_CONTESTANT_BASE,
            ['person_id' => $this->perContestant, 'contest_id' => 1, 'year' => 1]
        );

        $this->eventId = $this->createEvent([]);
        $taskId = $this->insert('fyziklani_task', [
            'event_id' => $this->eventId,
            'label' => 'AA',
            'name' => 'tmp',
        ]);

        $teamId = $this->insert('e_fyziklani_team', [
            'event_id' => $this->eventId,
            'name' => 'bar',
            'status' => 'applied',
            'category' => 'C',
        ]);
        $this->submitId = $this->insert('fyziklani_submit', [
            'fyziklani_task_id' => $taskId,
            'e_fyziklani_team_id' => $teamId,
            'points' => 5,
        ]);

        $this->mockApplication();
    }

    protected function tearDown(): void
    {
        $this->truncateTables([DbNames::TAB_CONTESTANT_BASE]);
        parent::tearDown();
    }

    public function getTestData(): array
    {
        return [
            [null, 'Fyziklani:Submit', ['create', 'edit', 'list'], false],
            ['perPerson', 'Fyziklani:Submit', ['create', 'edit', 'list'], false],
            ['perOrg', 'Fyziklani:Submit', ['create', 'list'], true], # TODO 'edit',
            ['perOrgOther', 'Fyziklani:Submit', ['create', 'edit', 'list'], false],
            ['perContestant', 'Fyziklani:Submit', ['create', 'edit', 'list'], false],
        ];
    }

    private function createGetRequest(string $presenterName, string $action): Request
    {
        $params = [
            'lang' => 'cs',
            'contestId' => (string)1,
            'year' => (string)1,
            'eventId' => (string)$this->eventId,
            'action' => $action,
            'id' => (string)$this->submitId,
        ];

        return new Request($presenterName, 'GET', $params);
    }

    /**
     * @dataProvider getTestData
     */
    public function testAccess(?string $personCol, string $presenterName, array $actions, bool $results): void
    {
        if (!is_array($actions)) {
            $actions = [$actions];
        }
        if (!is_array($results)) {
            $results = array_fill(0, count($actions), $results);
        }
        $presenter = $this->createPresenter($presenterName);
        if ($personCol) {
            /* Use indirect access because data provider is called before test set up. */
            $this->authenticate($this->{$personCol}, $presenter);
        }

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
            } elseif (!$forbidden) {
                Assert::type(RedirectResponse::class, $response);
                /** @var RedirectResponse $response */
                $url = $response->getUrl();
                Assert::contains('login', $url);
            }
        }
    }
}

$testCase = new Authorization($container);
$testCase->run();
