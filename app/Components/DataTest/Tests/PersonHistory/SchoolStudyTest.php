<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest\Tests\PersonHistory;

use FKSDB\Components\DataTest\Test;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Models\StudyYear;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<PersonHistoryModel>
 */
class SchoolStudyTest extends Test
{
    public function getTitle(): Title
    {
        return new Title(null, _('School study'));
    }

    /**
     * @param PersonHistoryModel $model
     */
    public function run(Logger $logger, Model $model): void
    {
        if ($model->school) {
            if ($model->study_year_new->isPrimarySchool()) {
                if (!$model->school->study_p) {
                    $this->addError($logger, $model);
                }
            } elseif ($model->study_year_new->isHighSchool()) {
                if (!$model->school->study_h) {
                    $this->addError($logger, $model);
                }
            } elseif ($model->study_year_new->value = StudyYear::UniversityAll) {
                if (!$model->school->study_u) {
                    $this->addError($logger, $model);
                }
            }
        }
    }

    private function addError(Logger $logger, PersonHistoryModel $history): void
    {
        $logger->log(
            new Message(
                sprintf(
                    _('School "%s" does not teach %s study year'),
                    $history->school->name,
                    $history->study_year_new->value
                ),
                Message::LVL_ERROR
            )
        );
    }
}
