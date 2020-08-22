<?php

namespace FKSDB\Tests\PresentersTests\PublicModule\SubmitPresenter;

$container = require '../../../bootstrap.php';

class AllUpload extends SubmitTestCase {

    protected function setUp(): void {
        parent::setUp();
        $this->createPersonHistory($this->personId, 2000, 1, 6);
    }

    public function testSubmit(): void {
        $this->innerTestSubmit();

        $this->assertSubmit($this->contestantId, $this->taskRestricted);
    }
}

$testCase = new AllUpload($container);
$testCase->run();
