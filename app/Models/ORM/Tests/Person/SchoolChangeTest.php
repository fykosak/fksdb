<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Person;

use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Components\DataTest\TestMessage;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<PersonModel>
 */
final class SchoolChangeTest extends Test
{
    public function getTitle(): Title
    {
        return new Title(null, _('School change'));
    }

    public function getDescription(): ?string
    {
        return _('Check if person changes primary school or high school during the study.');
    }

    protected function innerRun(TestLogger $logger, Model $model, string $id): void
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
                    $this->addErrorChange($logger, $history, $id);
                }
                $primarySchool = $history->school ?? $primarySchool;
            }
            if ($history->study_year_new->isHighSchool()) {
                if ($highSchool && $history->school_id && $highSchool->school_id !== $history->school_id) {
                    $this->addErrorChange($logger, $history, $id);
                }
                $highSchool = $history->school ?? $highSchool;
            }
        }
    }

    private function addErrorChange(TestLogger $logger, PersonHistoryModel $history, string $id): void
    {
        $logger->log(
            new TestMessage(
                $id,
                sprintf(
                    _('School changed in year %d'),
                    $history->ac_year
                ),
                Message::LVL_WARNING
            )
        );
    }

    public function getId(): string
    {
        return 'personSchoolChange';
    }
}
