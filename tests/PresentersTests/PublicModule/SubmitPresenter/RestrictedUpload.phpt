<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PublicModule\SubmitPresenter;

use FKSDB\Models\YearCalculator;

$container = require '../../../Bootstrap.php';

class RestrictedUpload extends SubmitTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->createPersonHistory($this->personId, YearCalculator::getCurrentAcademicYear(), 1, 9);
    }

    public function testSubmit(): void
    {
        $this->innerTestSubmit();
        $this->assertNotSubmit($this->contestantId, $this->taskRestricted);
    }
}

$testCase = new RestrictedUpload($container);
$testCase->run();
