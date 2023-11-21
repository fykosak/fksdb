<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest\Tests\Event\Schedule;

use FKSDB\Components\DataTest\Tests\Test;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<ScheduleItemModel>
 */
class RunOutCapacity extends Test
{
    /**
     * @param ScheduleItemModel $model
     */
    public function run(Logger $logger, Model $model): void
    {
        if (is_null($model->capacity)) {
            return;
        }
        $total = $model->capacity;
        $used = $model->getUsedCapacity();
        if ($used > $total * 0.9) {
            $logger->log(
                new Message(
                    _('Item has less then 10% free capacity'),
                    Message::LVL_WARNING
                )
            );
        }
    }

    public function getTitle(): Title
    {
        return new Title(null, _('Run out capacity'));
    }

    public function getId(): string
    {
        return 'RunOutCapacity';
    }
}
