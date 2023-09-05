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

/**
 * @phpstan-extends Test<PersonModel>
 */
class SchoolTest extends Test
{
    public function __construct()
    {
        parent::__construct(_('School study'));
    }

    /**
     * @param PersonModel $model
     */
    public function run(Logger $logger, Model $model): void
    {
        $histories = $model->getHistories();

        /** @var PersonHistoryModel $history */
        foreach ($histories as $history) {
            if ($history->school) {
                if ($history->study_year_new->isPrimarySchool()) {
                    if (!$history->school->study_p) {
                        $this->addError($logger, $history);
                    }
                } elseif ($history->study_year_new->isHighSchool()) {
                    if (!$history->school->study_h) {
                        $this->addError($logger, $history);
                    }
                } elseif ($history->study_year_new->value = StudyYear::UniversityAll) {
                    if (!$history->school->study_u) {
                        $this->addError($logger, $history);
                    }
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
