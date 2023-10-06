<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest\Tests\School;

use FKSDB\Components\DataTest\Test;
use FKSDB\Models\ORM\Models\SchoolModel;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<SchoolModel>
 */
class StudyYearFillTest extends Test
{
    public function getTitle(): Title
    {
        return new Title(null, _('Filled study years'));
    }

    public function getDescription(): ?string
    {
        return _('Check if school has filled any of study types (study_* fields)');
    }

    /**
     * @param SchoolModel $model
     */
    public function run(Logger $logger, Model $model): void
    {
        if ($model->active && !$model->study_p && !$model->study_h && !$model->study_u) {
            $logger->log(new Message(_('Missing study years'), Message::LVL_ERROR));
        }
    }
}
