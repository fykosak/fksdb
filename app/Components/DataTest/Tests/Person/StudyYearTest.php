<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest\Tests\Person;

use FKSDB\Components\DataTest\Test;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\StudyYear;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<PersonModel>
 */
class StudyYearTest extends Test
{
    public function getTitle(): Title
    {
        return new Title(null, _('Graduation years'));
    }

    public function getDescription(): ?string
    {
        return _('Compares graduation years of each year, checks if they are the same.');
    }

    /**
     * @param PersonModel $model
     */
    public function run(Logger $logger, Model $model): void
    {
        $histories = $model->getHistories()->order('ac_year');
        /** @var PersonHistoryModel|null $firstValid */
        $firstValid = null;
        /** @var PersonHistoryModel $history */
        foreach ($histories as $history) {
            if ($firstValid === null) {
                $firstValid = $history;
                continue;
            }
            if ($firstValid->getGraduationYear() !== $history->getGraduationYear()) {
                if (
                    $firstValid->study_year_new->value === StudyYear::Primary5 &&
                    $history->study_year_new->value === StudyYear::Primary5
                ) {
                    $level = Message::LVL_WARNING;
                } else {
                    $level = Message::LVL_ERROR;
                }
                $logger->log(
                    new Message(
                        sprintf(
                            'In %d expected graduation "%s" given "%s"',
                            $history->ac_year,
                            $firstValid->getGraduationYear(),
                            $history->getGraduationYear()
                        ),
                        $level
                    )
                );
                $firstValid = $history;
            }
        }
    }
}
