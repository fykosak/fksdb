<?php

$container = require '../bootstrap.php';

use Nette\Application\Request;
use Nette\Config\Helpers;
use Nette\DI\Container;
use PublicModule\SubmitTestCase;
use Tester\Assert;

class SubmitPresenterTest extends SubmitTestCase {

    function __construct(Container $container) {
        parent::__construct($container);
        $this->setContainer($container);
    }

    protected function setUp() {
        parent::setUp();
        $this->createPersonHistory($this->personId, 2000, 1, 9);
    }


    public function testSubmit() {
        $request = $this->createPostRequest(array(
            '_token_' => self::TOKEN,
        ));

        $request->setFiles(array(
            "task{$this->taskAll}" => $this->createFileUpload(),
        ));
        $response = $this->fixture->run($request);

        Assert::type('Nette\Application\Responses\ReactResponse', $response);
        var_dump((string)$response);

        $this->assertSubmit($this->contestantId, $this->taskAll);
    }

    protected function createPostRequest($postData, $post = array()) {
        $post = Helpers::merge($post, array(
            'action' => 'ajax',
            'lang' => 'cs',
            'contestId' => 1,
            'year' => 1,
            'act' => 'upload',
        ));

        $request = new Request('Public:Submit', 'POST', $post, $postData);
        //$request->setFlag(Request::SECURED);

        return $request;
    }

}

$testCase = new SubmitPresenterTest($container);
$testCase->run();
