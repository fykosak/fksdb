<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\FyziklaniModule;

// phpcs:disable
$container = require '../../Bootstrap.php';

// phpcs:enable
use FKSDB\Models\Authorization\Roles\Events\ExplicitEventRole;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\ContestantService;
use FKSDB\Models\ORM\Services\EventGrantService;
use FKSDB\Models\ORM\Services\EventOrganizerService;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use FKSDB\Models\ORM\Services\Fyziklani\TaskService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\ORM\Services\OrganizerService;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Nette\Utils\DateTime;
use Tester\Assert;

class Authorization extends FyziklaniTestCase
{
    private PersonModel $person;
    private PersonModel $contestOrganizer;
    private PersonModel $contestOrganizerOther;
    private PersonModel $contestant;
    private PersonModel $eventOrganizer;
    private PersonModel $inserter;

    private SubmitModel $submit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->person = $this->createPerson('Karkulka', 'Červená', [
            'email' => 'karkulka@les.cz',
            'born' => DateTime::from('2000-01-01'),
        ], []);

        $this->contestOrganizer = $this->createPerson('Karkulka', 'Červená', [
            'email' => 'karkulka2@les.cz',
            'born' => DateTime::from('2000-01-01'),
        ], []);
        $this->container->getByType(OrganizerService::class)->storeModel(
            ['person_id' => $this->contestOrganizer, 'contest_id' => 1, 'since' => 0, 'order' => 0]
        );

        $this->contestOrganizerOther = $this->createPerson('Karkulka', 'Červená', [
            'email' => 'karkulka3@les.cz',
            'born' => DateTime::from('2000-01-01'),
        ], []);
        $this->container->getByType(OrganizerService::class)->storeModel(
            ['person_id' => $this->contestOrganizerOther, 'contest_id' => 2, 'since' => 0, 'order' => 0]
        );


        $this->contestant = $this->createPerson('Karkulka', 'Červená', [
            'email' => 'karkulka4@les.cz',
            'born' => DateTime::from('2000-01-01'),
        ], []);
        $this->container->getByType(ContestantService::class)->storeModel(
            ['person_id' => $this->contestant, 'contest_id' => 1, 'year' => 1, 'contest_category_id' => 1]
        );

        $this->event = $this->createEvent([]);
        /** @var TaskModel $task */
        $task = $this->container->getByType(TaskService::class)->storeModel([
            'event_id' => $this->event->event_id,
            'label' => 'AA',
            'name' => 'tmp',
        ]);
        /** @var TeamModel2 $team */
        $team = $this->container->getByType(TeamService2::class)->storeModel([
            'event_id' => $this->event->event_id,
            'name' => 'bar',
            'status' => 'applied',
            'category' => 'C',
        ]);
        $this->submit = $this->container->getByType(SubmitService::class)->storeModel([
            'fyziklani_task_id' => $task->fyziklani_task_id,
            'fyziklani_team_id' => $team->fyziklani_team_id,
            'points' => 5,
        ]);
        $this->eventOrganizer = $this->createPerson('Karkulka', 'Červená', [
            'email' => 'karkulka5@les.cz',
            'born' => DateTime::from('2000-01-01'),
        ], []);
        $this->container->getByType(EventOrganizerService::class)->storeModel(
            ['person_id' => $this->eventOrganizer, 'event_id' => $this->event->event_id]
        );
        $this->inserter = $this->createPerson('Karkulka', 'Červená', [
            'email' => 'karkulka6@les.cz',
            'born' => DateTime::from('2000-01-01'),
        ], []);
        $this->container->getByType(EventGrantService::class)->storeModel([
            'login_id' => $this->inserter->getLogin()->login_id,
            'role' => ExplicitEventRole::GameInserter,
            'event_id' => $this->event->event_id,
        ]);
    }

    public function getTestData(): array
    {
        return [
            [fn() => null, 'Game:Submit', ['create', 'edit', 'list'], false],
            [fn() => $this->person, 'Game:Submit', ['create', 'edit', 'list'], false],
            [fn() => $this->contestOrganizer, 'Game:Submit', ['create', 'list'], false], # TODO 'edit',
            [fn() => $this->contestOrganizerOther, 'Game:Submit', ['create', 'edit', 'list'], false],
            [fn() => $this->contestant, 'Game:Submit', ['create', 'edit', 'list'], false],
            [fn() => $this->eventOrganizer, 'Game:Submit', ['create', 'edit', 'list'], false],
            [fn() => $this->inserter, 'Game:Submit', ['create', 'edit', 'list'], true],
        ];
    }

    private function createGetRequest(string $presenterName, string $action): Request
    {
        $params = [
            'lang' => 'cs',
            'contestId' => '1',
            'year' => '1',
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
        $presenter = $this->createPresenter($presenterName);
        if ($person()) {
            /* Use indirect access because data provider is called before test set up. */
            $this->authenticatePerson($person(), $presenter);
        }

        foreach ($actions as $i => $action) {
            $request = $this->createGetRequest($presenterName, $action);
            $response = null;

            try {
                $response = $presenter->run($request);
            } catch (ForbiddenRequestException $e) {
                if (!$results) {
                    continue;
                }
                Assert::null($e);
            }
            if ($results) {
                Assert::type(TextResponse::class, $response);
            } else {
                /** @var RedirectResponse $response */
                Assert::type(RedirectResponse::class, $response);
                $url = $response->getUrl();
                Assert::contains('login', $url);
            }
        }
    }
}
// phpcs:disable
$testCase = new Authorization($container);
$testCase->run();
// phpcs:enable
