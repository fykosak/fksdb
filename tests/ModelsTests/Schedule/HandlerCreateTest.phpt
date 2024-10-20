<?php

declare(strict_types=1);

namespace FKSDB\Tests\ModelsTests\Schedule;

// phpcs:disable
$container = require '../../Bootstrap.php';

// phpcs:enable
use FKSDB\Components\Schedule\Input\FullCapacityException;
use Tester\Assert;

class HandlerCreateTest extends HandlerTestCase
{

    public function testCapacityOk(): void
    {
        $this->personToItem($this->item1, 2);
        $this->personToItem($this->item2, 2);
        $this->personToItem($this->item3, 2);
        $this->handler->saveGroup($this->tester, $this->group->schedule_group_id, $this->item2->schedule_item_id, []);
        Assert::equal(3, $this->item2->getInterested()->count('*'));
    }

    public function testCapacityNotOk(): void
    {
        $this->personToItem($this->item1, 5);
        Assert::exception(
            fn() => $this->handler->saveGroup($this->tester, $this->group->schedule_group_id, $this->item1->schedule_item_id, []),
            FullCapacityException::class
        );
    }
    // test that count(*) is cached, but cont() isn't
    public function testCapacityTransaction(): void
    {
        $this->personToItem($this->item1, 4);
        $person2 = $this->createPerson('test', 'test2');
        $this->groupService->explorer->getConnection()->beginTransaction();
        Assert::equal(4, $this->item1->getUsedCapacity());
        $this->handler->saveGroup($person2, $this->group->schedule_group_id, $this->item1->schedule_item_id, []);

        Assert::exception(
            fn() => $this->handler->saveGroup($this->tester, $this->group->schedule_group_id, $this->item1->schedule_item_id, []),
            FullCapacityException::class
        );
        Assert::equal(5, $this->item1->getUsedCapacity());
        $this->groupService->explorer->getConnection()->commit();
    }

    public function testOverBook(): void
    {
        $this->personToItem($this->item1, 10);
        $this->personToItem($this->item2, 10);
        $this->personToItem($this->item3, 3);
        Assert::exception(
            fn() => $this->handler->saveGroup($this->tester, $this->group->schedule_group_id, $this->item3->schedule_item_id, []),
            FullCapacityException::class
        );
    }
}

// phpcs:disable
$testCase = new HandlerCreateTest($container);
$testCase->run();
// phpcs:enable
