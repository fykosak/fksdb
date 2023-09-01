<?php

declare(strict_types=1);

namespace FKSDB\Models\DataTesting\Tests\ModelPerson;

use FKSDB\Models\DataTesting\TestLog;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\StudyYear;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;

class StudyYearTest extends PersonTest
{
    public const STUDY_YEARS = [
        StudyYear::Primary5,
        StudyYear::Primary6,
        StudyYear::Primary7,
        StudyYear::Primary8,
        StudyYear::Primary9,
        StudyYear::High1,
        StudyYear::High2,
        StudyYear::High3,
        StudyYear::High4,
    ];

    public function __construct()
    {
        parent::__construct('study_year', _('Study years'));
    }

    public function run(Logger $logger, PersonModel $person): void
    {
        $histories = $person->getHistories()->order('ac_year');
        $expected = null;
        $hasError = false;
        /** @var PersonHistoryModel $history */
        foreach ($histories as $history) {
            $newExpected = $history->getGraduationYear();
            if ($newExpected === null) {
                continue;
            }
            if ($expected === null) {
                $expected = $newExpected;
                continue;
            }
            if ($expected !== $newExpected) {
                $hasError = true;
                $logger->log(
                    new TestLog(
                        $this->title,
                        sprintf('In %d expected graduated "%s" given "%s"', $history->ac_year, $expected, $newExpected),
                        Message::LVL_ERROR
                    )
                );
            }
        }
        if (!$hasError) {
            $logger->log(
                new TestLog(
                    $this->title,
                    'Study years OK',
                    Message::LVL_SUCCESS
                )
            );
        }
    }
}
