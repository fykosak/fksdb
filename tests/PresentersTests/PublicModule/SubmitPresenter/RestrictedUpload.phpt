<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PublicModule\SubmitPresenter;

use FKSDB\Models\ORM\Services\ContestYearService;

// phpcs:disable
$container = require '../../../Bootstrap.php';

// phpcs:enable
class RestrictedUpload extends SubmitTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->createPersonHistory(
            $this->person,
            ContestYearService::getCurrentAcademicYear(),
            $this->genericSchool,
            9
        );
    }

    public function testSubmit(): void
    {
        $this->innerTestSubmit();
        $this->assertNotSubmit($this->contestant, $this->taskRestricted);
    }
}
// phpcs:disable
$testCase = new RestrictedUpload($container);
$testCase->run();
// phpcs:enable
