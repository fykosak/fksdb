<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PublicModule\SubmitPresenter;

use FKSDB\Models\YearCalculator;

$container = require '../../../Bootstrap.php';

class AllUpload extends SubmitTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->createPersonHistory($this->personId, YearCalculator::getCurrentAcademicYear(), 1, 6);
    }

    public function testSubmit(): void
    {
        $this->innerTestSubmit();

        $this->assertSubmit($this->contestantId, $this->taskRestricted);
    }
}

$testCase = new AllUpload($container);
$testCase->run();
