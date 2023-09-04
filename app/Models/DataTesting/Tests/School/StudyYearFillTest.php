<?php

declare(strict_types=1);

namespace FKSDB\Models\DataTesting\Tests\School;

use FKSDB\Models\DataTesting\Test;
use FKSDB\Models\ORM\Models\SchoolModel;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;

/**
 * @phpstan-extends Test<SchoolModel>
 */
class StudyYearFillTest extends Test
{
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
