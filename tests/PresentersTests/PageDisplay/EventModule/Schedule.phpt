<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PageDisplay\EventModule;

use DateTime;
use FKSDB\Models\ORM\DbNames;

$container = require '../../../Bootstrap.php';

/**
 * Class EventModule
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Schedule extends EventModuleTestCase
{

    private int $scheduleGroupId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scheduleGroupId = $this->insert(DbNames::TAB_SCHEDULE_GROUP, [
            'schedule_group_type' => 'accommodation',
            'name_cs' => 'name CS',
            'name_en' => 'name EN',
            'event_id' => $this->eventId,
            'start' => new DateTime(),
            'end' => new DateTime(),
        ]);
    }

    protected function getEventData(): array
    {
        return [
            'event_type_id' => 1,
            'year' => 1,
            'event_year' => 1,
            'begin' => new DateTime(),
            'end' => new DateTime(),
            'name' => 'TEST FOF',
        ];
    }

    protected function transformParams(string $presenterName, string $action, array $params): array
    {
        [$presenterName, $action, $params] = parent::transformParams($presenterName, $action, $params);
        $params['id'] = $this->scheduleGroupId;
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

    protected function tearDown(): void
    {
        $this->truncateTables(['schedule_group', DbNames::TAB_EVENT]);
        parent::tearDown();
    }
}

$testCase = new Schedule($container);
$testCase->run();
