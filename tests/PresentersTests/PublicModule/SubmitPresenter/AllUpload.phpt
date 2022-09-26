<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PublicModule\SubmitPresenter;

use FKSDB\Models\ORM\Services\ContestYearService;

$container = require '../../../Bootstrap.php';

class AllUpload extends SubmitTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->createPersonHistory(
            $this->person,
            ContestYearService::getCurrentAcademicYear(),
            $this->genericSchool,
            6
        );
    }

    public function testSubmit(): void
    {
        $this->innerTestSubmit();

        $this->assertSubmit($this->contestant, $this->taskRestricted);
    }
}

$testCase = new AllUpload($container);
$testCase->run();
