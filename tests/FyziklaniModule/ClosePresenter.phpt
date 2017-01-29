<?php

namespace FyziklaniModule;

$container = require '../bootstrap.php';

use Events\Model\ApplicationHandler;
use FyziklaniModule\FyziklaniTestCase;
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
        $this->teamIds[] = $this->createTeam(array('e_fyziklani_team_id' => 1));
        $this->teamIds[] = $this->createTeam(array('e_fyziklani_team_id' => 2));
        $this->teamIds[] = $this->createTeam(array('e_fyziklani_team_id' => 3));

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

        /* Remaining setup stuff */
        $this->fixture = $this->createPresenter('Fyziklani:Close');
        $this->mockApplication();

        $this->container->parameters[BasePresenter::EVENT_NAME][$this->eventId] = &$this->container->parameters[BasePresenter::EVENT_NAME][1];
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
                    'do' => 'closeForm-submit',
        ));

        $request = new Request('Fyziklani:Close', 'POST', $post, $postData);
        return $request;
    }

    public function getTestTeams() {
        /* team ID, sum of points */
        return array(
            array(1, 15),
            array(2, 3),
            array(3, 3),
        );
    }

    /**
     * @dataProvider getTestTeams
     */
    public function testCloseTeam($teamId, $pointsSum) {
        $request = $this->createPostRequest(array(
            'id' => $teamId,
            'submit_task_correct' => 'on',
            'next_task_correct' => 'on',
            'send' => 'Potvrdit správnost',
                ), array(
            'id' => $teamId,
            'action' => 'team'));

        $response = $this->fixture->run($request);
        Assert::type('Nette\Application\Responses\RedirectResponse', $response);

        $team = $this->findTeam($teamId);
        Assert::notEqual(false, $team);
        Assert::equal($pointsSum, $team->points);
    }


}

$testCase = new ClosePresenterTest($container);
$testCase->run();
