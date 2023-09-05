<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest\Tests\School;

use FKSDB\Components\DataTest\Test;
use FKSDB\Models\ORM\Models\SchoolModel;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;

/**
 * @phpstan-extends Test<SchoolModel>
 */
class StudyYearFillTest extends Test
{
    public function __construct()
    {
        parent::__construct(_('Filled study years'));
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
