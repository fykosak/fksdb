<?php

declare(strict_types=1);

namespace FKSDB\Tests\ModelsTests\Schedule;

// phpcs:disable
$container = require '../../Bootstrap.php';

// phpcs:enable
use FKSDB\Components\Forms\Controls\Schedule\FullCapacityException;
use FKSDB\Components\Forms\Controls\Schedule\ScheduleException;
use Tester\Assert;

class HandlerModifyTest extends HandlerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->personScheduleService->storeModel(
            ['person_id' => $this->tester->person_id, 'schedule_item_id' => $this->item1->schedule_item_id]
        );
    }

    public function testCapacityOk(): void
    {
        $this->personToItem($this->item1, 2);
        $this->personToItem($this->item2, 2);
        $this->personToItem($this->item3, 2);
        $this->handler->saveGroup($this->tester, $this->group, $this->item2->schedule_item_id);
        Assert::equal(3, $this->item2->getInterested()->count('*'));
        Assert::equal(2, $this->item1->getInterested()->count('*'));
    }

    public function testCapacityNotOk(): void
    {
        $this->personToItem($this->item1, 2);
        $this->personToItem($this->item2, 5);
        $this->personToItem($this->item3, 2);
        Assert::exception(
            fn() => $this->handler->saveGroup($this->tester, $this->group, $this->item2->schedule_item_id),
            FullCapacityException::class
        );
    }

    public function testOverbook(): void
    {
        $this->personToItem($this->item1, 10);
        $this->personToItem($this->item2, 10);
        $this->personToItem($this->item3, 2);
        $this->handler->saveGroup($this->tester, $this->group, $this->item3->schedule_item_id);
        Assert::equal(10, $this->item1->getInterested()->count('*'));
        Assert::equal(3, $this->item3->getInterested()->count('*'));
    }

    public function testRegistrationEnd(): void
    {
        $this->groupService->storeModel(
            ['registration_end' => (new \DateTime())->sub(new \DateInterval('P1D'))],
            $this->group
        );
        $this->personToItem($this->item1, 2);
        $this->personToItem($this->item2, 2);
        $this->personToItem($this->item3, 2);
        Assert::exception(
            fn() => $this->handler->saveGroup($this->tester, $this->group, $this->item3->schedule_item_id),
            ScheduleException::class
        );
        Assert::equal(2, $this->item3->getInterested()->count('*'));
    }

    public function testModificationEnd(): void
    {
        $this->groupService->storeModel(
            ['modification_end' => (new \DateTime())->sub(new \DateInterval('P1D'))],
            $this->group
        );
        $this->personToItem($this->item1, 2);
        $this->personToItem($this->item2, 2);
        $this->personToItem($this->item3, 2);
        Assert::exception(
            fn() => $this->handler->saveGroup($this->tester, $this->group, $this->item3->schedule_item_id),
            ScheduleException::class
        );
        Assert::equal(2, $this->item3->getInterested()->count('*'));
    }

    public function testBetweenDates(): void
    {
        $this->groupService->storeModel(
            [
                'modification_end' => (new \DateTime())->add(new \DateInterval('P1D')),
                'registration_end' => (new \DateTime())->sub(new \DateInterval('P1D')),
            ],
            $this->group
        );
        $this->personToItem($this->item1, 2);
        $this->personToItem($this->item2, 2);
        $this->personToItem($this->item3, 2);
        $this->handler->saveGroup($this->tester, $this->group, $this->item3->schedule_item_id);
        Assert::equal(3, $this->item3->getInterested()->count('*'));
    }
}

// phpcs:disable
$testCase = new HandlerModifyTest($container);
$testCase->run();
// phpcs:enable
