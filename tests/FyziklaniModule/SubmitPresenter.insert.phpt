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

class SubmitPresenterTest extends FyziklaniTestCase {

    use MockApplicationTrait;

    /**
     * @var ApplicationHandler
     */
    private $fixture;

    private $teamId;

    private $taskId;

    function __construct(Container $container) {
        parent::__construct($container);
        $this->setContainer($container);
    }

    protected function setUp() {
        parent::setUp();

        $this->eventId = $this->createEvent(array());
        $this->teamId = $this->createTeam(array('e_fyziklani_team_id' => 1));
        $this->taskId = $this->createTask(array('label' => 'AA'));


        $this->fixture = $this->createPresenter('Fyziklani:Submit');
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
                    'do' => 'entryForm-submit',
        ));

        $request = new Request('Fyziklani:Submit', 'POST', $post, $postData);
        return $request;
    }

    public function testEntry() {
        $request = $this->createPostRequest(array(
            'taskCode' => '000001AA9',
            'points5' => '5 bodů',
                ), array('action' => 'entry'));

        $response = $this->fixture->run($request);
        Assert::type('Nette\Application\Responses\RedirectResponse', $response);

        $submit = $this->assertSubmit($this->taskId, $this->teamId);
        Assert::equal(5, $submit->points);
    }

}

$testCase = new SubmitPresenterTest($container);
$testCase->run();
