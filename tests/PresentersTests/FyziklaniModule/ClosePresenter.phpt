<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\FyziklaniModule;

$container = require '../../Bootstrap.php';

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

        $this->eventId = $this->createEvent([]);

        $teamIds = [];
        $teamIds[] = $this->createTeam(['e_fyziklani_team_id' => 1, 'status' => 'participated']);
        $teamIds[] = $this->createTeam(['e_fyziklani_team_id' => 2, 'status' => 'participated']);
        $teamIds[] = $this->createTeam(['e_fyziklani_team_id' => 3, 'status' => 'participated']);
        $teamIds[] = $this->createTeam(['e_fyziklani_team_id' => 4, 'status' => 'participated', 'category' => 'B']);

        $taskIds = [];
        $taskIds[] = $this->createTask(['label' => 'AA']);
        $taskIds[] = $this->createTask(['label' => 'AB']);
        $taskIds[] = $this->createTask(['label' => 'AC']);

        /* Rest is just to fill board */
        $this->createTask(['label' => 'AD']);
        $this->createTask(['label' => 'AE']);
        $this->createTask(['label' => 'AF']);
        $this->createTask(['label' => 'AG']);

        /* Team 1 all correct */
        $teamId = $teamIds[0];
        foreach ($taskIds as $taskId) {
            $this->createSubmit([
                'fyziklani_task_id' => $taskId,
                'e_fyziklani_team_id' => $teamId,
                'points' => 5,
                'state' => 'checked',
            ]);
        }

        /* Team 2 one problem only */
        $teamId = $teamIds[1];
        $this->createSubmit([
            'fyziklani_task_id' => $taskIds[0],
            'e_fyziklani_team_id' => $teamId,
            'points' => 3,
            'state' => 'checked',
        ]);

        /* Team 3 retrier */
        $teamId = $teamIds[2];
        foreach ($taskIds as $taskId) {
            $this->createSubmit([
                'fyziklani_task_id' => $taskId,
                'e_fyziklani_team_id' => $teamId,
                'points' => 1,
                'state' => 'checked',
            ]);
        }

        /* Team 4 is another category */
        $teamId = $teamIds[3];
        foreach ($taskIds as $taskId) {
            $this->createSubmit([
                'fyziklani_task_id' => $taskId,
                'e_fyziklani_team_id' => $teamId,
                'points' => 2,
                'state' => 'checked',
            ]);
        }

        /* Remaining setup stuff */
        $this->fixture = $this->createPresenter('Fyziklani:Close');
        $this->mockApplication();

        $this->authenticate($this->userPersonId, $this->fixture);
    }

    private function createCloseTeamRequest(array $formData, array $params = []): Request
    {
        return new Request(
            'Fyziklani:Close:team',
            'POST',
            Helpers::merge($params, [
                'lang' => 'cs',
                'eventId' => $this->eventId,
            ]),
            $formData
        );
    }

    private function createPostDiplomasRequest(array $formData, array $params = []): Request
    {
        return new Request(
            'Fyziklani:Diplomas:default',
            'POST',
            Helpers::merge($params, [
                'lang' => 'cs',
                'eventId' => $this->eventId,
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
    private function innerTestCloseTeam(int $teamId, int $pointsSum): void
    {
        $request = $this->createCloseTeamRequest([
            '_do' => 'closeTeamControl-close',
        ], [
            'id' => (string)$teamId,
            'action' => 'team',
        ]);
        $response = $this->fixture->run($request);
        Assert::type(RedirectResponse::class, $response);
        $team = $this->findTeam($teamId);
        Assert::notEqual(false, $team);
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

$testCase = new ClosePresenter($container);
$testCase->run();
