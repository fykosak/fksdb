<?php

namespace FKSDB\Tests\PageDisplay;

use FKSDB\ORM\DbNames;

$container = require '../bootstrap.php';

/**
 * Class OrgModule
 * @author Michal Červeňák <miso@fykos.cz>
 */
class OrgModule extends AbstractPageDisplayTestCase {
    protected function setUp() {
        parent::setUp();
        $this->insert(DbNames::TAB_ORG, ['person_id' => self::PERSON_ID, 'contest_id' => 1, 'since' => 1, 'order' => 1]);

    }

    public function getPages(): array {
        return [
            ['Org:Contestant', ['contestId' => 1, 'year' => 1, 'action' => 'create']],
            ['Org:Contestant', ['contestId' => 1, 'year' => 1, 'action' => 'list']],
            ['Org:Dashboard', ['contestId' => 1, 'year' => 1]],
            ['Org:Event', ['contestId' => 1, 'year' => 1, 'action' => 'create']],
            ['Org:Event', ['contestId' => 1, 'year' => 1, 'action' => 'list']],
            ['Org:Export', ['contestId' => 1, 'year' => 1, 'action' => 'compose']],
            ['Org:Export', ['contestId' => 1, 'year' => 1, 'action' => 'list']],
            ['Org:Inbox', ['contestId' => 1, 'year' => 1, 'action' => 'corrected', 'series' => 1]],
            ['Org:Inbox', ['contestId' => 1, 'year' => 1, 'action' => 'default', 'series' => 1]],
            ['Org:Inbox', ['contestId' => 1, 'year' => 1, 'action' => 'handout', 'series' => 1]],
            ['Org:Inbox', ['contestId' => 1, 'year' => 1, 'action' => 'inbox', 'series' => 1]],
            ['Org:Inbox', ['contestId' => 1, 'year' => 1, 'action' => 'list', 'series' => 1]],
            ['Org:Org', ['contestId' => 1, 'year' => 1, 'action' => 'list']],
            ['Org:Org', ['contestId' => 1, 'year' => 1, 'action' => 'create']],
            ['Org:Points', ['contestId' => 1, 'year' => 1, 'action' => 'entry', 'series' => 1]],
            ['Org:Points', ['contestId' => 1, 'year' => 1, 'action' => 'preview', 'series' => 1]],
            ['Org:Tasks', ['contestId' => 1, 'year' => 1, 'action' => 'import']],
            ['Org:Teacher', ['contestId' => 1, 'year' => 1, 'action' => 'list']],
            ['Org:Teacher', ['contestId' => 1, 'year' => 1, 'action' => 'create']],
        ];
    }

    protected function tearDown() {
        $this->connection->query('DELETE FROM org');
        parent::tearDown();
    }
}

$testCase = new OrgModule($container);
$testCase->run();
