<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\PersonHistory;

use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Components\DataTest\TestMessage;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Models\StudyYear;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<PersonHistoryModel>
 */
final class StudyTypeTest extends Test
{
    public function getTitle(): Title
    {
        return new Title(null, _('Study type'));
    }

    public function getDescription(): ?string
    {
        return _('Checks if school provides study type filled in study_year field.');
    }

    /**
     * @param PersonHistoryModel $model
     */
    protected function innerRun(TestLogger $logger, Model $model, string $id): void
    {
        if ($model->school) {
            if ($model->study_year_new->isPrimarySchool() && !$model->school->study_p) {
                $this->addError($logger, $model, $id);
            } elseif ($model->study_year_new->isHighSchool() && !$model->school->study_h) {
                $this->addError($logger, $model, $id);
            } elseif ($model->study_year_new->value === StudyYear::UniversityAll && !$model->school->study_u) {
                $this->addError($logger, $model, $id);
            }
        }
    }

    private function addError(TestLogger $logger, PersonHistoryModel $history, string $id): void
    {
        $logger->log(
            new TestMessage(
                $id,
                sprintf(
                    _('School "%s" does not teach %s study year.'),
                    $history->school->name,
                    $history->study_year_new->value
                ),
                Message::LVL_ERROR
            )
        );
    }

    public function getId(): string
    {
        return 'personHistoryStudyType';
    }
}
