<?php

$container = require '../bootstrap.php';

use Nette\Application\Responses\RedirectResponse;
use Nette\DI\Container;
use FKSDB\PublicModule\SubmitTestCase;
use Tester\Assert;

class SubmitPresenterTest extends SubmitTestCase {

    protected function setUp() {
        parent::setUp();
        $this->createPersonHistory($this->personId, 2000, 1, 9);
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

        $this->assertNotSubmit($this->contestantId, $this->taskRestricted);
    }

}

$testCase = new SubmitPresenterTest($container);
$testCase->run();
