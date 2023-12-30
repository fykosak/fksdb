<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Event\Schedule;

use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Components\DataTest\TestMessage;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<ScheduleItemModel>
 */
final class RunOutCapacity extends Test
{
    /**
     * @param ScheduleItemModel $model
     */
    public function run(TestLogger $logger, Model $model, string $id): void
    {
        if (is_null($model->capacity)) {
            return;
        }
        $total = $model->capacity;
        $used = $model->getUsedCapacity();
        if ($used > $total * 0.9) {
            $logger->log(
                new TestMessage(
                    $id,
                    _('Item has less then 10% free capacity'),
                    Message::LVL_WARNING
                )
            );
        }
    }

    public function getTitle(): Title
    {
        return new Title(null, _('Run out of capacity'));
    }

    public function getId(): string
    {
        return 'runOutCapacity';
    }
}
