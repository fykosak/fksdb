<?php

declare(strict_types=1);

namespace FKSDB\Tests\ModelsTests\Schedule;

// phpcs:disable
$container = require '../../Bootstrap.php';

// phpcs:enable
use FKSDB\Components\Forms\Controls\Schedule\FullCapacityException;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use FKSDB\Models\ORM\Services\Schedule\ScheduleItemService;
use Tester\Assert;

class HandlerCreateTest extends HandlerTestCase
{

    public function testCapacityOk(): void
    {
        $this->personToItem($this->item1, 2);
        $this->personToItem($this->item2, 2);
        $this->personToItem($this->item3, 2);
        $this->handler->saveGroup($this->tester, $this->group, $this->item2->schedule_item_id);

        Assert::equal(3, $this->item2->getInterested()->count('*'));
    }

    public function testCapacityNotOk(): void
    {
        $this->personToItem($this->item1, 5);

        Assert::exception(
            fn() => $this->handler->saveGroup($this->tester, $this->group, $this->item1->schedule_item_id),
            FullCapacityException::class
        );
    }

    public function testOverBook(): void
    {

        $this->personToItem($this->item1, 10);
        $this->personToItem($this->item2, 10);
        $this->personToItem($this->item3, 3);

        Assert::exception(
            fn() => $this->handler->saveGroup($this->tester, $this->group, $this->item3->schedule_item_id),
            FullCapacityException::class
        );
    }
}

// phpcs:disable
$testCase = new HandlerCreateTest($container);
$testCase->run();

// phpcs:enable
