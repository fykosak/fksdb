<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\ContestYear;

use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Components\DataTest\TestMessage;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<ContestYearModel>
 */
final class InActiveContest extends Test
{
    protected function innerRun(TestLogger $logger, Model $model, string $id): void
    {
        if (!$model->isActive()) {
            $logger->log(
                new TestMessage(
                    $id,
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
        return 'inactiveContest';
    }
}
