<?php

namespace FKSDB\Tests\PageDisplay;

use FKSDB\ORM\DbNames;

$container = require '../bootstrap.php';

/**
 * Class CommonModule
 * @author Michal Červeňák <miso@fykos.cz>
 */
class CommonModule extends AbstractPageDisplayTestCase {

    protected function setUp() {
        parent::setUp();
        $this->insert(DbNames::TAB_PERSON_INFO, ['person_id' => self::PERSON_ID]);
    }

    public function getPages(): array {
        return [['Common:Chart', ['action' => 'list']],
            ['Common:Dashboard', []],
            ['Common:Deduplicate', ['action' => 'person']],
            ['Common:Person', ['action' => 'create']],
            ['Common:Person', ['action' => 'edit', 'id' => self::PERSON_ID]],
            ['Common:Person', ['action' => 'detail', 'id' => self::PERSON_ID]],
            ['Common:Person', ['action' => 'pizza']],
            ['Common:Person', ['action' => 'search']],
            ['Common:School', ['action' => 'list']],
            ['Common:School', ['action' => 'create']],
            ['Common:Spam', ['action' => 'list']],
            ['Common:Validation', []],
            ['Common:Validation', ['action' => 'list']],
            ['Common:Validation', ['action' => 'preview']],
        ];
    }

    protected function tearDown() {
        $this->connection->query('DELETE FROM person_info');
        parent::tearDown();
    }
}

$testCase = new CommonModule($container);
$testCase->run();
