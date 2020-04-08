<?php

$container = require '../bootstrap.php';

use Nette\Application\Responses\RedirectResponse;
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
        $this->createPersonHistory($this->personId, 2000, 1, 6);
    }

    public function testSubmit() {
        $request = $this->createPostRequest([
            'upload' => 'Odeslat',
            'tasks' => "{$this->taskAll},{$this->taskRestricted}",
            '_token_' => self::TOKEN,
        ]);

        $request->setFiles([
            "task{$this->taskAll}" => $this->createFileUpload(),
            "task{$this->taskRestricted}" => $this->createFileUpload(),
        ]);
        $response = $this->fixture->run($request);

        Assert::type(RedirectResponse::class, $response);
        dump($response);

        $this->assertSubmit($this->contestantId, $this->taskAll);

        $this->assertSubmit($this->contestantId, $this->taskRestricted);
    }

}

$testCase = new SubmitPresenterTest($container);
$testCase->run();
