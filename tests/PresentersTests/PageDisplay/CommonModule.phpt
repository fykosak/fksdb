<?php

namespace FKSDB\Tests\PresentersTests\PageDisplay;

use FKSDB\ORM\DbNames;

$container = require '../../bootstrap.php';

/**
 * Class CommonModule
 * @author Michal Červeňák <miso@fykos.cz>
 */
class CommonModule extends AbstractPageDisplayTestCase {

    protected function setUp() {
        parent::setUp();
        $this->insert(DbNames::TAB_PERSON_INFO, ['person_id' => $this->personId]);
    }

    protected function transformParams(string $presenterName, string $action, array $params): array {
        list($presenterName, $action, $params) = parent::transformParams($presenterName, $action, $params);
        if ($presenterName === 'Common:Person') {
            $params['id'] = $this->personId;
        }
        return [$presenterName, $action, $params];
    }

    public function getPages(): array {

        return [
            ['Common:Chart', 'list'],
            ['Common:Chart', 'totalPersons'],
            ['Common:Dashboard', 'default'],
            ['Common:Deduplicate', 'person'],
            ['Common:Person', 'create'],
            ['Common:Person', 'edit'],
            ['Common:Person', 'detail'],
            ['Common:Person', 'pizza'],
            ['Common:Person', 'search'],
            ['Common:School', 'list'],
            ['Common:School', 'create'],
            ['Common:Spam', 'list'],
            ['Common:Validation', 'default'],
            ['Common:Validation', 'list'],
            ['Common:Validation', 'preview'],
        ];
    }

    protected function tearDown() {
        $this->connection->query('DELETE FROM person_info');
        parent::tearDown();
    }
}

$testCase = new CommonModule($container);
$testCase->run();
