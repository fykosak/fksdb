<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PageDisplay\EventModule;

use FKSDB\Models\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\Models\ORM\Services\Schedule\ServiceScheduleGroup;

$container = require '../../../Bootstrap.php';

class Schedule extends EventModuleTestCase
{

    private ModelScheduleGroup $scheduleGroup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scheduleGroup = $this->getContainer()
            ->getByType(ServiceScheduleGroup::class)
            ->createNewModel([
            'schedule_group_type' => 'accommodation',
            'name_cs' => 'name CS',
            'name_en' => 'name EN',
            'event_id' => $this->event->event_id,
            'start' => new \DateTime(),
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
        ];
    }

    protected function transformParams(string $presenterName, string $action, array $params): array
    {
        [$presenterName, $action, $params] = parent::transformParams($presenterName, $action, $params);
        $params['id'] = $this->scheduleGroup->schedule_group_id;
        return [$presenterName, $action, $params];
    }

    public function getPages(): array
    {
        return [
            ['Event:ScheduleGroup', 'list'],
            ['Event:ScheduleGroup', 'persons'],
            ['Event:ScheduleGroup', 'create'],
            ['Event:ScheduleGroup', 'detail'],
            ['Event:ScheduleGroup', 'edit'],
        ];
    }
}

$testCase = new Schedule($container);
$testCase->run();
