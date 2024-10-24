<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PublicModule\SubmitPresenter;

use FKSDB\Models\ORM\Models\StudyYear;
use FKSDB\Models\ORM\Services\ContestYearService;

// phpcs:disable
$container = require '../../../Bootstrap.php';

// phpcs:enable

class AllUpload extends SubmitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createPersonHistory(
            $this->person,
            ContestYearService::getCurrentAcademicYear(),
            $this->genericSchool,
            $this->getStudyYear(),
        );
    }

    public function testSubmit(): void
    {
        $this->innerTestSubmit();

        $this->assertSubmit($this->contestant, $this->taskRestricted);
    }

    protected function getStudyYear(): string
    {
        return StudyYear::Primary6;
    }
}

// phpcs:disable
$testCase = new AllUpload($container);
$testCase->run();
// phpcs:enable
