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
class SetSchoolTest extends Test
{
    public function getTitle(): Title
    {
        return new Title(null, _('School set'));
    }

    public function getDescription(): ?string
    {
        return _('Check if school is filled when study year is filled.');
    }

    /**
     * @param PersonHistoryModel $model
     */
    public function run(Logger $logger, Model $model): void
    {
        if ($model->study_year_new->value !== StudyYear::None && !$model->school_id) {
            $this->addError($logger, $model);
        }
    }

    private function addError(Logger $logger, PersonHistoryModel $history): void
    {
        $logger->log(
            new Message(
                sprintf(
                    _('School is required for primary and high schools study in year %d'),
                    $history->ac_year
                ),
                Message::LVL_ERROR
            )
        );
    }
}
