<?php

namespace FKSDB\Tests\PresentersTests\PublicModule\SubmitPresenter;

$container = require '../../../bootstrap.php';

use Nette\Application\Responses\RedirectResponse;
use Tester\Assert;

class RestrictedUpload extends SubmitTestCase {

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

        $this->assertSubmit($this->contestantId, $this->taskAll);

        $this->assertNotSubmit($this->contestantId, $this->taskRestricted);
    }
}

$testCase = new RestrictedUpload($container);
$testCase->run();
