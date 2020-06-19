<?php

namespace FKSDB\Tests\PageDisplay;

use FKSDB\ORM\DbNames;

$container = require '../bootstrap.php';

/**
 * Class FyziklaniModule
 * @author Michal Červeňák <miso@fykos.cz>
 */
class FyziklaniModule extends AbstractPageDisplayTestCase {

    const EVENT_ID = 1;

    protected function setUp() {
        parent::setUp();
        $this->connection->query("INSERT INTO event_type (event_type_id, contest_id, name) VALUES (1, 1, 'Fyziklání')");

        $this->insert(DbNames::TAB_EVENT, [
            'event_id' => self::EVENT_ID,
            'event_type_id' => 1,
            'year' => 1,
            'event_year' => 1,
            'begin' => new \DateTime(),
            'end' => new \DateTime(),
            'name' => 'TEST FOF',
        ]);
        $this->insert(DbNames::TAB_FYZIKLANI_GAME_SETUP, [
            'event_id' => self::EVENT_ID,
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
            ['Fyziklani:Close', ['eventId' => self::EVENT_ID, 'action' => 'list']],
            ['Fyziklani:Dashboard', ['eventId' => self::EVENT_ID, 'action' => 'default']],
            ['Fyziklani:Diplomas', ['eventId' => self::EVENT_ID, 'action' => 'default']],
            ['Fyziklani:Diplomas', ['eventId' => self::EVENT_ID, 'action' => 'results']],

            ['Fyziklani:GameSetup', ['eventId' => self::EVENT_ID, 'action' => 'default']],

            ['Fyziklani:Results', ['eventId' => self::EVENT_ID, 'action' => 'correlationStatistics']],
            ['Fyziklani:Results', ['eventId' => self::EVENT_ID, 'action' => 'list']],
            ['Fyziklani:Results', ['eventId' => self::EVENT_ID, 'action' => 'presentation']],
            ['Fyziklani:Results', ['eventId' => self::EVENT_ID, 'action' => 'table']],
            ['Fyziklani:Results', ['eventId' => self::EVENT_ID, 'action' => 'taskStatistics']],
            ['Fyziklani:Results', ['eventId' => self::EVENT_ID, 'action' => 'teamStatistics']],

            ['Fyziklani:Submit', ['eventId' => self::EVENT_ID, 'action' => 'create']],
            ['Fyziklani:Submit', ['eventId' => self::EVENT_ID, 'action' => 'list']],

            ['Fyziklani:Task', ['eventId' => self::EVENT_ID, 'action' => 'import']],
            ['Fyziklani:Task', ['eventId' => self::EVENT_ID, 'action' => 'list']],
        ];
    }

    protected function tearDown() {
        $this->connection->query('DELETE FROM fyziklani_game_setup');
        $this->connection->query('DELETE FROM event');
        $this->connection->query('DELETE FROM event_type');
        parent::tearDown();
    }
}

$testCase = new FyziklaniModule($container);
$testCase->run();
