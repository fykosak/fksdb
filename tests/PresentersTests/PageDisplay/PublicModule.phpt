<?php

namespace FKSDB\Tests\PresentersTests\PageDisplay;

use FKSDB\ORM\DbNames;

$container = require '../../bootstrap.php';

/**
 * Class OrgModule
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PublicModule extends AbstractPageDisplayTestCase {
    protected function setUp() {
        parent::setUp();
        $this->insert(DbNames::TAB_CONTESTANT_BASE, ['person_id' => $this->personId, 'contest_id' => 1, 'year' => 1]);
    }

    protected function transformParams(string $presenterName, string $action, array $params): array {
        list($presenterName, $action, $params) = parent::transformParams($presenterName, $action, $params);
        $params['year'] = 1;
        $params['contestId'] = 1;
        return [$presenterName, $action, $params];
    }
    
    public function getPages(): array {
        return [
            ['Public:Application', 'list'],
            ['Public:Dashboard', 'default'],
            ['Public:Submit', 'ajax'],
            ['Public:Submit', 'default'],
        ];
    }

    protected function tearDown() {
        $this->connection->query('DELETE FROM contestant_base');
        parent::tearDown();
    }
}

$testCase = new PublicModule($container);
$testCase->run();
