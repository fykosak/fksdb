<?php

namespace FyziklaniModule;

$container = require '../bootstrap.php';

use FKSDB\Events\Model\ApplicationHandler;
use MockEnvironment\MockApplicationTrait;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Nette\Config\Helpers;
use Nette\DI\Container;
use Tester\Assert;

class ClosePresenterTest extends FyziklaniTestCase {

    use MockApplicationTrait;

    /**
     * @var ApplicationHandler
     */
    private $fixture;

    private $teamIds;

    private $taskIds;

    function __construct(Container $container) {
        parent::__construct($container);
        $this->setContainer($container);
    }

    protected function setUp() {
        parent::setUp();

        $this->eventId = $this->createEvent([]);

        $this->teamIds = [];
        $this->teamIds[] = $this->createTeam(['e_fyziklani_team_id' => 1, 'status' => 'participated']);
        $this->teamIds[] = $this->createTeam(['e_fyziklani_team_id' => 2, 'status' => 'participated']);
        $this->teamIds[] = $this->createTeam(['e_fyziklani_team_id' => 3, 'status' => 'participated']);
        $this->teamIds[] = $this->createTeam(['e_fyziklani_team_id' => 4, 'status' => 'participated', 'category' => 'B']);

        $this->taskIds = [];
        $this->taskIds[] = $this->createTask(['label' => 'AA']);
        $this->taskIds[] = $this->createTask(['label' => 'AB']);
        $this->taskIds[] = $this->createTask(['label' => 'AC']);

        /* Rest is just to fill board */
        $this->createTask(['label' => 'AD']);
        $this->createTask(['label' => 'AE']);
        $this->createTask(['label' => 'AF']);
        $this->createTask(['label' => 'AG']);

        /* Team 1 all correct */
        $teamId = $this->teamIds[0];
        foreach ($this->taskIds as $taskId) {
            $this->createSubmit([
                'fyziklani_task_id' => $taskId,
                'e_fyziklani_team_id' => $teamId,
                'points' => 5,
                'state' => 'checked',
            ]);
        }

        /* Team 2 one problem only */
        $teamId = $this->teamIds[1];
        $this->createSubmit([
            'fyziklani_task_id' => $this->taskIds[0],
            'e_fyziklani_team_id' => $teamId,
            'points' => 3,
            'state' => 'checked',
        ]);

        /* Team 3 retrier */
        $teamId = $this->teamIds[2];
        foreach ($this->taskIds as $taskId) {
            $this->createSubmit([
                'fyziklani_task_id' => $taskId,
                'e_fyziklani_team_id' => $teamId,
                'points' => 1,
                'state' => 'checked',
            ]);
        }

        /* Team 4 is another category */
        $teamId = $this->teamIds[3];
        foreach ($this->taskIds as $taskId) {
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

        $this->authenticate($this->userPersonId);
    }

    protected function tearDown() {
        parent::tearDown();
    }

    private function createPostRequest($postData, $post = []) {
        $post = Helpers::merge($post, [
            'lang' => 'cs',
            'contestId' => 1,
            'year' => 1,
            'eventId' => $this->eventId,
        ]);

        return new Request('Fyziklani:Close', 'POST', $post, $postData);

    }

    private function createPostDiplomasRequest($postData, $post = []): Request {
        $post = Helpers::merge($post, [
            'lang' => 'cs',
            'contestId' => 1,
            'year' => 1,
            'eventId' => $this->eventId,
        ]);

        return new Request('Fyziklani:Diplomas:default', 'POST', $post, $postData);
    }

    public function getTestTeams($category) {
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

    public function getCategories() {
        return [
            ['A'],
            ['B'],
        ];
    }

    /**
     * Not a real test method.
     */
    private function innertestCloseTeam($teamId, $pointsSum) {
        $request = $this->createPostRequest([
            'id' => $teamId,
        ], [
            'id' => $teamId,
            'action' => 'team',
            'do' => 'closeTeamControl-close',
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
    public function testCloseCategory($category) {
        foreach ($this->getTestTeams($category) as $teamData) {
            list($teamId, $pointsSum,) = $teamData;
            $this->innertestCloseTeam($teamId, $pointsSum);
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

    public function testCloseAll() {
        foreach ($this->getCategories() as $catData) {
            list($category) = $catData;
            foreach ($this->getTestTeams($category) as $teamData) {
                list($teamId, $pointsSum, $cRank, $rank) = $teamData;
                $this->innertestCloseTeam($teamId, $pointsSum);
            }
        }

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

$testCase = new ClosePresenterTest($container);
$testCase->run();
