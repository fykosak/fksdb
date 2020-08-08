<?php

namespace FKSDB\Tests\PresentersTests\PageDisplay;

use FKSDB\ORM\DbNames;
use FKSDB\Tests\PresentersTests\PageDisplay\EventModule\EventModuleTestCase;

$container = require '../../bootstrap.php';

/**
 * Class FyziklaniModule
 * @author Michal Červeňák <miso@fykos.cz>
 */
class FyziklaniModule extends EventModuleTestCase {
    protected function getEventData(): array {
        return [
            'event_type_id' => 1,
            'year' => 1,
            'event_year' => 1,
            'begin' => new \DateTime(),
            'end' => new \DateTime(),
            'name' => 'TEST FOF',
        ];
    }

    protected function setUp() {
        parent::setUp();
        $this->insert(DbNames::TAB_FYZIKLANI_GAME_SETUP, [
            'event_id' => $this->eventId,
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

    public function getPages(): array {
        return [
            ['Fyziklani:Close', 'list'],
            ['Fyziklani:Dashboard', 'default'],
            ['Fyziklani:Diplomas', 'default',],
            ['Fyziklani:Diplomas', 'results'],
            ['Fyziklani:GameSetup', 'default',],
            ['Fyziklani:Results', 'correlationStatistics'],
            ['Fyziklani:Results', 'list'],
            ['Fyziklani:Results', 'presentation'],
            ['Fyziklani:Results', 'table'],
            ['Fyziklani:Results', 'taskStatistics'],
            ['Fyziklani:Results', 'teamStatistics'],
            ['Fyziklani:Submit', 'create'],
            ['Fyziklani:Submit', 'list'],
            ['Fyziklani:Task', 'import'],
            ['Fyziklani:Task', 'list'],
        ];
    }

    protected function tearDown() {
        $this->connection->query('DELETE FROM fyziklani_game_setup');
        parent::tearDown();
    }
}

$testCase = new FyziklaniModule($container);
$testCase->run();
