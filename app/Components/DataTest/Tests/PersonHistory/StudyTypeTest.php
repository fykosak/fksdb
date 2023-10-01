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
class StudyTypeTest extends Test
{
    public function getTitle(): Title
    {
        return new Title(null, _('Study type'));
    }

    public function getDescription(): ?string
    {
        return _('Checks if school provides study type filled in study_year field');
    }

    /**
     * @param PersonHistoryModel $model
     */
    public function run(Logger $logger, Model $model): void
    {
        if ($model->school) {
            if ($model->study_year_new->isPrimarySchool() && !$model->school->study_p) {
                $this->addError($logger, $model);
            } elseif ($model->study_year_new->isHighSchool() && !$model->school->study_h) {
                $this->addError($logger, $model);
            } elseif ($model->study_year_new->value === StudyYear::UniversityAll && !$model->school->study_u) {
                $this->addError($logger, $model);
            }
        }
    }

    private function addError(Logger $logger, PersonHistoryModel $history): void
    {
        $logger->log(
            new Message(
                sprintf(
                    _('School "%s" does not teach %s study year.'),
                    $history->school->name,
                    $history->study_year_new->value
                ),
                Message::LVL_ERROR
            )
        );
    }
}