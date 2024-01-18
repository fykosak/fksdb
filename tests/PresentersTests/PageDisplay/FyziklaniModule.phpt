<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PageDisplay;

use FKSDB\Models\ORM\Services\Fyziklani\GameSetupService;
use FKSDB\Tests\PresentersTests\PageDisplay\EventModule\EventModuleTestCase;

// phpcs:disable
$container = require '../../Bootstrap.php';

// phpcs:enable
class FyziklaniModule extends EventModuleTestCase
{

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

    protected function setUp(): void
    {
        parent::setUp();
        $this->container->getByType(GameSetupService::class)->storeModel([
            'event_id' => $this->event->event_id,
            'game_start' => new \DateTime(),
            'result_display' => new \DateTime(),
            'result_hide' => new \DateTime(),
            'game_end' => new \DateTime(),
            'refresh_delay' => 1000,
            'tasks_on_board' => 7,
            'result_hard_display' => false,
            'available_points' => '5,3,2,1',
        ]);
    }

    public function getPages(): array
    {
        return [
            ['Game:Close', 'list'],
            ['Game:Dashboard', 'default'],
            ['Game:Diplomas', 'default',],
            ['Game:Diplomas', 'results'],
            ['Game:GameSetup', 'default'],
            ['Game:Statistics', 'table'],
            ['Game:Statistics', 'team'],
            ['Game:Statistics', 'task'],
            ['Game:Statistics', 'correlation'],
            ['Game:Presentation', 'default'],
            ['Game:Submit', 'create'],
            ['Game:Submit', 'list'],
            ['Game:Task', 'list'],
            ['Game:Seating', 'default'],
            ['Game:Seating', 'print'],
        ];
    }
}

// phpcs:disable
$testCase = new FyziklaniModule($container);
$testCase->run();
// phpcs:enable
