<?php

namespace FyziklaniModule;

$container = require '../bootstrap.php';

use Events\Model\ApplicationHandler;
use MockEnvironment\MockApplicationTrait;
use Nette\Application\Request;
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

        $this->eventId = $this->createEvent(array());

        $this->teamIds = array();
        $this->teamIds[] = $this->createTeam(array('e_fyziklani_team_id' => 1, 'status' => 'participated'));
        $this->teamIds[] = $this->createTeam(array('e_fyziklani_team_id' => 2, 'status' => 'participated'));
        $this->teamIds[] = $this->createTeam(array('e_fyziklani_team_id' => 3, 'status' => 'participated'));
        $this->teamIds[] = $this->createTeam(array('e_fyziklani_team_id' => 4, 'status' => 'participated', 'category' => 'B'));

        $this->taskIds = array();
        $this->taskIds[] = $this->createTask(array('label' => 'AA'));
        $this->taskIds[] = $this->createTask(array('label' => 'AB'));
        $this->taskIds[] = $this->createTask(array('label' => 'AC'));

        /* Rest is just to fill board */
        $this->createTask(array('label' => 'AD'));
        $this->createTask(array('label' => 'AE'));
        $this->createTask(array('label' => 'AF'));
        $this->createTask(array('label' => 'AG'));

        /* Team 1 all correct */
        $teamId = $this->teamIds[0];
        foreach ($this->taskIds as $taskId) {
            $this->createSubmit(array(
                'fyziklani_task_id' => $taskId,
                'e_fyziklani_team_id' => $teamId,
                'points' => 5,
            ));
        }

        /* Team 2 one problem only */
        $teamId = $this->teamIds[1];
        $this->createSubmit(array(
            'fyziklani_task_id' => $this->taskIds[0],
            'e_fyziklani_team_id' => $teamId,
            'points' => 3,
        ));

        /* Team 3 retrier */
        $teamId = $this->teamIds[2];
        foreach ($this->taskIds as $taskId) {
            $this->createSubmit(array(
                'fyziklani_task_id' => $taskId,
                'e_fyziklani_team_id' => $teamId,
                'points' => 1,
            ));
        }

        /* Team 4 is another category */
        $teamId = $this->teamIds[3];
        foreach ($this->taskIds as $taskId) {
            $this->createSubmit(array(
                'fyziklani_task_id' => $taskId,
                'e_fyziklani_team_id' => $teamId,
                'points' => 2,
            ));
        }

        /* Remaining setup stuff */
        $this->fixture = $this->createPresenter('Fyziklani:Close');
        $this->mockApplication();

        $this->authenticate($this->userPersonId);
    }

    protected function tearDown() {
        parent::tearDown();
    }

    private function createPostRequest($postData, $post = array()) {
        $post = Helpers::merge($post, array(
            'lang' => 'cs',
            'contestId' => 1,
            'year' => 1,
            'eventId' => $this->eventId,
        ));

        $request = new Request('Fyziklani:Close', 'POST', $post, $postData);
        return $request;
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
        $request = $this->createPostRequest(array(
            'id' => $teamId,
            'submit_task_correct' => 'on',
            'next_task_correct' => 'on',
            'send' => 'Potvrdit správnost',
        ), array(
            'id' => $teamId,
            'action' => 'team',
            'do' => 'closeTeamControl-form-submit',
        ));

        $response = $this->fixture->run($request);
        Assert::type('Nette\Application\Responses\RedirectResponse', $response);
        $team = $this->findTeam($teamId);
        Assert::notEqual(false, $team);
        Assert::equal($pointsSum, $team->points);
    }

    /**
     * @dataProvider getCategories
     */
    public function testCloseCategory($category) {
        foreach ($this->getTestTeams($category) as $teamData) {
            list($teamId, $pointsSum, $cRank, $rank) = $teamData;
            $this->innertestCloseTeam($teamId, $pointsSum);
        }

        $request = $this->createPostRequest(array(
            'category' => $category,
            'send' => 'Uzavřít kategorii ' . $category . '.',
        ), array(
            'action' => 'table',
            'do' => 'closeControl-closeCategory' . $category . 'Form-form-submit',
        ));

        $response = $this->fixture->run($request);
        Assert::type('Nette\Application\Responses\RedirectResponse', $response);

        foreach ($this->getTestTeams($category) as $teamData) {
            list($teamId, $pointsSum, $cRank, $rank) = $teamData;
            $team = $this->findTeam($teamId);
            Assert::notEqual(false, $team);
            Assert::equal($cRank, $team->rank_category);
        }
    }

    public function testCloseAll() {
        foreach ($this->getCategories() as $catData) {
            list($category) = $catData;
            foreach ($this->getTestTeams($category) as $teamData) {
                list($teamId, $pointsSum, $cRank, $rank) = $teamData;
                $this->innertestCloseTeam($teamId, $pointsSum);
            }
        }

        $request = $this->createPostRequest(array(
            'send' => 'Uzavřít celé Fyziklání',
        ), array(
            'action' => 'table',
            'do' => 'closeControl-closeGlobalForm-form-submit',
        ));

        $response = $this->fixture->run($request);
        Assert::type('Nette\Application\Responses\RedirectResponse', $response);

        foreach ($this->getTestTeams($category) as $teamData) {
            list($teamId, $pointsSum, $cRank, $rank) = $teamData;
            $team = $this->findTeam($teamId);
            Assert::notEqual(false, $team);
            Assert::equal($rank, $team->rank_total);
        }
    }

}

$testCase = new ClosePresenterTest($container);
$testCase->run();
