<?php

declare(strict_types=1);

namespace FKSDB\Models\DataTesting\Tests\ModelPerson;

use FKSDB\Models\DataTesting\TestLog;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\StudyYear;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;

class SchoolTest extends PersonTest
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
        parent::__construct('school_study', _('School study'));
    }

    public function run(Logger $logger, PersonModel $person): void
    {
        $histories = $person->getHistories();

        /** @var PersonHistoryModel $history */
        foreach ($histories as $history) {
            if ($history->school) {
                if ($history->study_year_new->isPrimarySchool()) {
                    if (!$history->school->study_p) {
                        $this->addError($logger, $history);
                    } else {
                        $this->addSuccess($logger, $history);
                    }
                } elseif ($history->study_year_new->isHighSchool()) {
                    if (!$history->school->study_h) {
                        $this->addError($logger, $history);
                    } else {
                        $this->addSuccess($logger, $history);
                    }
                } elseif ($history->study_year_new->value = StudyYear::UniversityAll) {
                    if (!$history->school->study_u) {
                        $this->addError($logger, $history);
                    } else {
                        $this->addSuccess($logger, $history);
                    }
                }
            }
        }
    }

    private function addError(Logger $logger, PersonHistoryModel $history): void
    {
        $logger->log(
            new TestLog(
                sprintf(
                    _('School "%s" does not teach %s study year'),
                    $history->school->name,
                    $history->study_year_new->value
                ),
                Message::LVL_ERROR,
                $this->title,
            )
        );
    }

    private function addSuccess(Logger $logger, PersonHistoryModel $history): void
    {
        $logger->log(
            new TestLog(
                sprintf(
                    _('School "%s" teach %s study year'),
                    $history->school->name,
                    $history->study_year_new->value
                ),
                Message::LVL_SUCCESS,
                $this->title,
            )
        );
    }
}
