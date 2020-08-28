<?php

namespace FKSDB\Tests\PresentersTests\PublicModule\SubmitPresenter;

$container = require '../../../bootstrap.php';

class RestrictedUpload extends SubmitTestCase {

    protected function setUp(): void {
        parent::setUp();
        $this->createPersonHistory($this->personId, 2000, 1, 9);
    }

    public function testSubmit(): void {
        $this->innerTestSubmit();
        $this->assertNotSubmit($this->contestantId, $this->taskRestricted);
    }
}

$testCase = new RestrictedUpload($container);
$testCase->run();
