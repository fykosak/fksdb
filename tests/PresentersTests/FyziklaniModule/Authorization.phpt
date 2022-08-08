<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\FyziklaniModule;

$container = require '../../Bootstrap.php';

use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use FKSDB\Models\ORM\Services\Fyziklani\TaskService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\ORM\Services\ContestantService;
use FKSDB\Models\ORM\Services\OrgService;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Nette\Utils\DateTime;
use Tester\Assert;

class Authorization extends FyziklaniTestCase
{
    private PersonModel $perPerson;
    private PersonModel $perOrg;
    private PersonModel $perOrgOther;
    private PersonModel $perContestant;
    private SubmitModel $submit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->perPerson = $this->createPerson('Karkulka', 'Červená', [
            'email' => 'karkulka@les.cz',
            'born' => DateTime::from('2000-01-01'),
        ], []);

        $this->perOrg = $this->createPerson('Karkulka', 'Červená', [
            'email' => 'karkulka2@les.cz',
            'born' => DateTime::from('2000-01-01'),
        ], []);
        $this->getContainer()->getByType(OrgService::class)->createNewModel(
            ['person_id' => $this->perOrg, 'contest_id' => 1, 'since' => 0, 'order' => 0]
        );

        $this->perOrgOther = $this->createPerson('Karkulka', 'Červená', [
            'email' => 'karkulka3@les.cz',
            'born' => DateTime::from('2000-01-01'),
        ], []);
        $this->getContainer()->getByType(OrgService::class)->createNewModel(
            ['person_id' => $this->perOrgOther, 'contest_id' => 2, 'since' => 0, 'order' => 0]
        );

        $this->perContestant = $this->createPerson('Karkulka', 'Červená', [
            'email' => 'karkulka4@les.cz',
            'born' => DateTime::from('2000-01-01'),
        ], []);
        $this->getContainer()->getByType(ContestantService::class)->createNewModel(
            ['person_id' => $this->perContestant, 'contest_id' => 1, 'year' => 1]
        );

        $this->event = $this->createEvent([]);
        $task = $this->getContainer()->getByType(TaskService::class)->createNewModel([
            'event_id' => $this->event->event_id,
            'label' => 'AA',
            'name' => 'tmp',
        ]);

        $team = $this->getContainer()->getByType(TeamService2::class)->createNewModel([
            'event_id' => $this->event->event_id,
            'name' => 'bar',
            'status' => 'applied',
            'category' => 'C',
        ]);
        $this->submit = $this->getContainer()->getByType(SubmitService::class)->createNewModel([
            'fyziklani_task_id' => $task->fyziklani_task_id,
            'fyziklani_team_id' => $team->fyziklani_team_id,
            'points' => 5,
        ]);

        $this->mockApplication();
    }

    public function getTestData(): array
    {
        return [
            [fn() => null, 'Fyziklani:Submit', ['create', 'edit', 'list'], false],
            [fn() => $this->perPerson, 'Fyziklani:Submit', ['create', 'edit', 'list'], false],
            [fn() => $this->perOrg, 'Fyziklani:Submit', ['create', 'list'], true], # TODO 'edit',
            [fn() => $this->perOrgOther, 'Fyziklani:Submit', ['create', 'edit', 'list'], false],
            [fn() => $this->perContestant, 'Fyziklani:Submit', ['create', 'edit', 'list'], false],
        ];
    }

    private function createGetRequest(string $presenterName, string $action): Request
    {
        $params = [
            'lang' => 'cs',
            'contestId' => (string)1,
            'year' => (string)1,
            'eventId' => (string)$this->event->event_id,
            'action' => $action,
            'id' => (string)$this->submit->fyziklani_submit_id,
        ];

        return new Request($presenterName, 'GET', $params);
    }

    /**
     * @dataProvider getTestData
     */
    public function testAccess(callable $person, string $presenterName, array $actions, bool $results): void
    {
        if (!is_array($actions)) {
            $actions = [$actions];
        }
        if (!is_array($results)) {
            $results = array_fill(0, count($actions), $results);
        }
        $presenter = $this->createPresenter($presenterName);
        if ($person()) {
            /* Use indirect access because data provider is called before test set up. */
            $this->authenticatePerson($person(), $presenter);
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
