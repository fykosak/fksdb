<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PageDisplay\EventModule;

// phpcs:disable
$container = require '../../../Bootstrap.php';

// phpcs:enable
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\Schedule\ScheduleGroupService;
use FKSDB\Models\ORM\Services\Schedule\ScheduleItemService;

class Schedule extends EventModuleTestCase
{

    private ScheduleGroupModel $scheduleGroup;
    private ScheduleItemModel $scheduleItem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scheduleGroup = $this->container
            ->getByType(ScheduleGroupService::class)
            ->storeModel([
                'schedule_group_type' => 'accommodation',
                'name_cs' => 'name CS',
                'name_en' => 'name EN',
                'event_id' => $this->event->event_id,
                'start' => new \DateTime(),
                'end' => new \DateTime(),
            ]);
        $this->scheduleItem = $this->container
            ->getByType(ScheduleItemService::class)
            ->storeModel([
                'schedule_group_id' => $this->scheduleGroup->schedule_group_id,
                'price_czk' => 10.5,
                'price_eur' => 2.55,
                'name_cs' => 'test item',
                'name_en' => 'test item',
                'capacity' => 10,
                'begin' => new \DateTime(),
                'end' => new \DateTime(),
            ]);
    }

    protected function getEventData(): array
    {
        return [
            'event_type_id' => 1,
            'year' => 1,
            'event_year' => 1,
            'begin' => new \DateTime(),
            'end' => new \DateTime(),
            'name' => 'TEST FOF',
            'registration_begin' => new \DateTime(),
            'registration_end' => new \DateTime(),
        ];
    }

    protected function transformParams(string $presenterName, string $action, array $params): array
    {
        [$presenterName, $action, $params] = parent::transformParams($presenterName, $action, $params);
        if ($presenterName === 'Schedule:Group') {
            $params['id'] = $this->scheduleGroup->schedule_group_id;
        } elseif ($presenterName === 'Schedule:Item') {
            $params['id'] = $this->scheduleItem->schedule_item_id;
        }

        return [$presenterName, $action, $params];
    }

    public function getPages(): array
    {
        return [
            ['Schedule:Person', 'list'],
           // ['Schedule:Person', 'default'],
            ['Schedule:Item', 'create'],
            ['Schedule:Item', 'edit'],
            ['Schedule:Item', 'detail'],
            ['Schedule:Group', 'list'],
            ['Schedule:Group', 'create'],
            ['Schedule:Group', 'edit'],
            ['Schedule:Group', 'detail'],
        ];
    }
}

// phpcs:disable
$testCase = new Schedule($container);
$testCase->run();
// phpcs:enable
