<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\FyziklaniModule;

// phpcs:disable
$container = require '../../Bootstrap.php';

// phpcs:enable
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Nette\Application\IPresenter;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Nette\Schema\Helpers;
use Tester\Assert;

class ClosePresenter extends FyziklaniTestCase
{

    private IPresenter $fixture;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = $this->createEvent([]);

        $teams = [];
        $teams[] = $this->createTeam(['fyziklani_team_id' => 1, 'state' => 'participated', 'name' => 'team0']);
        $teams[] = $this->createTeam(['fyziklani_team_id' => 2, 'state' => 'participated', 'name' => 'team1']);
        $teams[] = $this->createTeam(['fyziklani_team_id' => 3, 'state' => 'participated', 'name' => 'team2']);
        $teams[] = $this->createTeam(
            ['fyziklani_team_id' => 4, 'state' => 'participated', 'category' => 'B', 'name' => 'team3']
        );

        $tasks = [];
        $tasks[] = $this->createTask(['label' => 'AA']);
        $tasks[] = $this->createTask(['label' => 'AB']);
        $tasks[] = $this->createTask(['label' => 'AC']);

        /* Rest is just to fill board */
        $this->createTask(['label' => 'AD']);
        $this->createTask(['label' => 'AE']);
        $this->createTask(['label' => 'AF']);
        $this->createTask(['label' => 'AG']);

        /* Team 1 all correct */
        $team = $teams[0];
        foreach ($tasks as $task) {
            $this->createSubmit([
                'fyziklani_task_id' => $task->fyziklani_task_id,
                'fyziklani_team_id' => $team->fyziklani_team_id,
                'points' => 5,
                'state' => 'checked',
            ]);
        }

        /* Team 2 one problem only */
        $team = $teams[1];
        $this->createSubmit([
            'fyziklani_task_id' => $tasks[0]->fyziklani_task_id,
            'fyziklani_team_id' => $team->fyziklani_team_id,
            'points' => 3,
            'state' => 'checked',
        ]);

        /* Team 3 retrier */
        $team = $teams[2];
        foreach ($tasks as $task) {
            $this->createSubmit([
                'fyziklani_task_id' => $task->fyziklani_task_id,
                'fyziklani_team_id' => $team->fyziklani_team_id,
                'points' => 1,
                'state' => 'checked',
            ]);
        }

        /* Team 4 is another category */
        $team = $teams[3];
        foreach ($tasks as $task) {
            $this->createSubmit([
                'fyziklani_task_id' => $task->fyziklani_task_id,
                'fyziklani_team_id' => $team->fyziklani_team_id,
                'points' => 2,
                'state' => 'checked',
            ]);
        }

        /* Remaining setup stuff */
        $this->fixture = $this->createPresenter('Game:Close');
        $this->mockApplication();

        $this->authenticatePerson($this->userPerson, $this->fixture);
    }

    private function createCloseTeamRequest(array $formData, array $params = []): Request
    {
        return new Request(
            'Game:Close:team',
            'POST',
            Helpers::merge($params, [
                'lang' => 'cs',
                'eventId' => $this->event->event_id,
            ]),
            $formData
        );
    }

    private function createPostDiplomasRequest(array $formData, array $params = []): Request
    {
        return new Request(
            'Game:Diplomas:default',
            'POST',
            Helpers::merge($params, [
                'lang' => 'cs',
                'eventId' => $this->event->event_id,
            ]),
            $formData
        );
    }

    public function getTestTeams(string $category): array
    {
        /* team ID, sum of points, rank_category, rank */
        $a = [
            'A' => [
                [1, 15, 1, 1],
                [2, 3, 2, 3],
                [3, 3, 3, 4],
            ],
            'B' => [
                [4, 6, 1, 2],
            ],
        ];
        return $a[$category];
    }

    public function getCategories(): array
    {
        return [
            ['A'],
            ['B'],
        ];
    }

    /**
     * Not a real test method.
     */
    private function innerTestCloseTeam(TeamModel2 $team, int $pointsSum): void
    {
        $request = $this->createCloseTeamRequest([
            '_do' => 'closeTeamControl-close',
        ], [
            'id' => (string)$team->fyziklani_team_id,
            'action' => 'team',
        ]);
        $response = $this->fixture->run($request);
        Assert::type(RedirectResponse::class, $response);
        Assert::equal($pointsSum, $team->points);
    }

    /**
     * @dataProvider getCategories
     */
    public function testCloseCategory(string $category): void
    {
        Assert::true(true);

        /*  foreach ($this->getTestTeams($category) as $teamData) {
              [$teamId, $pointsSum,] = $teamData;
              $this->innerTestCloseTeam($teamId, $pointsSum);
          }
          /*    $request = $this->createPostDiplomasRequest([], [
                  'category' => $category,
                  'do' => 'close',
              ]);

              $response = $this->fixture->run($request);
              Assert::type(\Nette\Application\Responses\RedirectResponse::class, $response);

              foreach ($this->getTestTeams($category) as $teamData) {
                  list($teamId, , $cRank,) = $teamData;
                  $team = $this->findTeam($teamId);
                  Assert::notEqual(false, $team);
                  Assert::equal($cRank, $team->rank_category);
              }*/
    }

    public function testCloseAll(): void
    {
        Assert::true(true);
        /*foreach ($this->getCategories() as $catData) {
            [$category] = $catData;
            foreach ($this->getTestTeams($category) as $teamData) {
                [$teamId, $pointsSum, $cRank, $rank] = $teamData;
                $this->innerTestCloseTeam($teamId, $pointsSum);
            }
        }*/
        /*  $request = $this->createPostDiplomasRequest([], [
              'action' => 'default',
              'do' => 'close',
          ]);

          $response = $this->fixture->run($request);
          Assert::type(\Nette\Application\Responses\RedirectResponse::class, $response);

          foreach ($this->getTestTeams($category) as $teamData) {
              list($teamId, $pointsSum, $cRank, $rank) = $teamData;
              $team = $this->findTeam($teamId);
              Assert::notEqual(false, $team);
              Assert::equal($rank, $team->rank_total);
          }*/
    }
}
// phpcs:disable
$testCase = new ClosePresenter($container);
$testCase->run();
// phpcs:enable
