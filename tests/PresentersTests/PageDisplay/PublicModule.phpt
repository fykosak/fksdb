<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PageDisplay;

use FKSDB\Models\ORM\DbNames;

$container = require '../../Bootstrap.php';

/**
 * Class OrgModule
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PublicModule extends AbstractPageDisplayTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->insert(DbNames::TAB_CONTESTANT_BASE, ['person_id' => $this->personId, 'contest_id' => 1, 'year' => 1]);
    }

    protected function transformParams(string $presenterName, string $action, array $params): array
    {
        [$presenterName, $action, $params] = parent::transformParams($presenterName, $action, $params);
        $params['year'] = '1';
        $params['contestId'] = '1';
        return [$presenterName, $action, $params];
    }

    public function getPages(): array
    {
        return [
            ['Public:Dashboard', 'default'],
            ['Public:Submit', 'default'],
            ['Public:Submit', 'ajax'],
        ];
    }

    protected function tearDown(): void
    {
        $this->truncateTables([DbNames::TAB_CONTESTANT_BASE]);
        parent::tearDown();
    }
}

$testCase = new PublicModule($container);
$testCase->run();
