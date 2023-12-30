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
final class SetSchoolTest extends Test
{
    public function getTitle(): Title
    {
        return new Title(null, _('School set'));
    }

    public function getDescription(): ?string
    {
        return _('Checks if school is filled when study year is filled.');
    }

    /**
     * @param PersonHistoryModel $model
     */
    public function run(TestLogger $logger, Model $model, string $id): void
    {
        if ($model->study_year_new->value !== StudyYear::None && !$model->school_id) {
            $this->addError($logger, $model, $id);
        }
    }

    private function addError(TestLogger $logger, PersonHistoryModel $history, string $id): void
    {
        $logger->log(
            new TestMessage(
                $id,
                sprintf(
                    _('School is required for primary and high school study in year %d'),
                    $history->ac_year
                ),
                Message::LVL_ERROR
            )
        );
    }
    public function getId(): string
    {
        return 'personHistorySetSchool';
    }
}
