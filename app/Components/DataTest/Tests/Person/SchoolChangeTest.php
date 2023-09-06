<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest\Tests\Person;

use FKSDB\Components\DataTest\Test;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\SchoolModel;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<PersonModel>
 */
class SchoolChangeTest extends Test
{
    public function getTitle(): Title
    {
        return new Title(null, _('School change'));
    }

    /**
     * @param PersonModel $model
     */
    public function run(Logger $logger, Model $model): void
    {
        $histories = $model->getHistories()->order('ac_year');
        /** @var SchoolModel|null $highSchool */
        $highSchool = null;
        /** @var SchoolModel|null $primarySchool */
        $primarySchool = null;
        /** @var PersonHistoryModel $history */
        foreach ($histories as $history) {
            if ($history->study_year_new->isPrimarySchool()) {
                if ($primarySchool && $history->school_id && $primarySchool->school_id !== $history->school_id) {
                    $this->addErrorChange($logger, $history);
                }
                $primarySchool = $history->school ?? $primarySchool;
            }
            if ($history->study_year_new->isHighSchool()) {
                if ($highSchool && $history->school_id && $highSchool->school_id !== $history->school_id) {
                    $this->addErrorChange($logger, $history);
                }
                $highSchool = $history->school ?? $highSchool;
            }
        }
    }

    private function addErrorChange(Logger $logger, PersonHistoryModel $history): void
    {
        $logger->log(
            new Message(
                sprintf(
                    _('Change school in year %d'),
                    $history->ac_year
                ),
                Message::LVL_WARNING
            )
        );
    }
}
