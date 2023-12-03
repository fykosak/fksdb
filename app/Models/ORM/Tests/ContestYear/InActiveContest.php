<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\ContestYear;

use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<ContestYearModel>
 */
class InActiveContest extends Test
{
    /**
     * @param ContestYearModel $model
     */
    public function run(Logger $logger, Model $model): void
    {
        if (!$model->isActive()) {
            $logger->log(
                new Message(
                    sprintf(_('Contest %s has not open submitting, please upload tasks!'), $model->contest->name),
                    Message::LVL_ERROR
                )
            );
        }
    }

    public function getTitle(): Title
    {
        return new Title(null, _('Inactive contest year'));
    }

    public function getId(): string
    {
        return 'InactiveContest';
    }
}
