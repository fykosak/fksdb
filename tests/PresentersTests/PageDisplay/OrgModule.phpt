<?php

namespace FKSDB\Tests\PresentersTests\PageDisplay;

use FKSDB\ORM\DbNames;

$container = require '../../bootstrap.php';

/**
 * Class OrgModule
 * @author Michal Červeňák <miso@fykos.cz>
 */
class OrgModule extends AbstractPageDisplayTestCase {
    protected function setUp() {
        parent::setUp();
        $this->insert(DbNames::TAB_ORG, ['person_id' => $this->personId, 'contest_id' => 1, 'since' => 1, 'order' => 1]);
    }

    protected function transformParams(string $presenterName, string $action, array $params): array {
        list($presenterName, $action, $params) = parent::transformParams($presenterName, $action, $params);
        $params['year'] = 1;
        $params['contestId'] = 1;
        $params['series'] = 1;
        return [$presenterName, $action, $params];
    }

    public function getPages(): array {
        return [
            ['Org:Contestant', 'create'],
            ['Org:Contestant', 'list'],
            ['Org:Dashboard', 'default'],
            ['Org:Event', 'create'],
            ['Org:Event', 'list'],
            ['Org:StoredQuery', 'create'],
            ['Org:StoredQuery', 'list'],
            ['Org:Inbox', 'corrected'],
            ['Org:Inbox', 'default'],
            ['Org:Inbox', 'handout'],
            ['Org:Inbox', 'inbox'],
            ['Org:Inbox', 'list'],
            ['Org:Org', 'list'],
            ['Org:Org', 'create'],
            ['Org:Points', 'entry'],
            ['Org:Points', 'preview'],
            ['Org:Tasks', 'import'],
            ['Org:Teacher', 'list'],
            ['Org:Teacher', 'create'],
        ];
    }

    protected function tearDown() {
        $this->connection->query('DELETE FROM org');
        parent::tearDown();
    }
}

$testCase = new OrgModule($container);
$testCase->run();
