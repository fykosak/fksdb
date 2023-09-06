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
        return new Title(null, _('Study years'));
    }

    /**
     * @param PersonModel $model
     */
    public function run(Logger $logger, Model $model): void
    {
        $histories = $model->getHistories()->order('ac_year');
        /** @var PersonHistoryModel|null $firstValid */
        $firstValid = null;
        /** @var PersonHistoryModel|null $postgraduate */
        $postgraduate = null;
        /** @var PersonHistoryModel $history */
        foreach ($histories as $history) {
            if ($history->getGraduationYear() === null) {
                $postgraduate = $history;
                continue;
            }
            if ($firstValid === null) {
                $firstValid = $history;
                continue;
            }
            if ($postgraduate) {
                $logger->log(
                    new Message(
                        sprintf(
                            'Before %d found postgraduate study year in %d',
                            $history->ac_year,
                            $postgraduate->ac_year
                        ),
                        Message::LVL_ERROR
                    )
                );
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
                            'In %d expected graduated "%s" given "%s"',
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
