<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PageDisplay;

use FKSDB\Models\ORM\Services\Fyziklani\GameSetupService;
use FKSDB\Tests\PresentersTests\PageDisplay\EventModule\EventModuleTestCase;

$container = require '../../Bootstrap.php';

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
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->getContainer()->getByType(GameSetupService::class)->createNewModel([
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
            ['Fyziklani:Close', 'list'],
            ['Fyziklani:Dashboard', 'default'],
            ['Fyziklani:Diplomas', 'default',],
            ['Fyziklani:Diplomas', 'results'],
            ['Fyziklani:GameSetup', 'default'],
            ['Fyziklani:Statistics', 'table'],
            ['Fyziklani:Statistics', 'team'],
            ['Fyziklani:Statistics', 'task'],
            ['Fyziklani:Statistics', 'correlation'],
            ['Fyziklani:Presentation', 'default'],
            ['Fyziklani:Submit', 'create'],
            ['Fyziklani:Submit', 'list'],
            ['Fyziklani:Task', 'list'],
            ['Fyziklani:Seating', 'list'],
            ['Fyziklani:Seating', 'print'],
        ];
    }
}

$testCase = new FyziklaniModule($container);
$testCase->run();
