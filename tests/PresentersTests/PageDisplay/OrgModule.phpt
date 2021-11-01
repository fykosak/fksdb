<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PageDisplay;

use FKSDB\Models\ORM\DbNames;

$container = require '../../Bootstrap.php';

/**
 * Class OrgModule
 * @author Michal Červeňák <miso@fykos.cz>
 */
class OrgModule extends AbstractPageDisplayTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->insert(DbNames::TAB_ORG, [
            'person_id' => $this->personId,
            'contest_id' => 1,
            'since' => 1,
            'order' => 1,
        ]);
        $this->insert(DbNames::TAB_PERSON_INFO, ['person_id' => $this->personId]);
    }

    protected function transformParams(string $presenterName, string $action, array $params): array
    {
        [$presenterName, $action, $params] = parent::transformParams($presenterName, $action, $params);
        $params['year'] = (string)1;
        $params['contestId'] = (string)1;
        $params['series'] = (string)1;
        if ($presenterName === 'Org:Person') {
            $params['id'] = (string)$this->personId;
        }
        return [$presenterName, $action, $params];
    }

    public function getPages(): array
    {
        return [
            ['Org:Inbox', 'corrected'],

            ['Org:Contestant', 'list'],

            ['Org:Inbox', 'handout'],
            ['Org:Inbox', 'inbox'],
            ['Org:Inbox', 'list'],

            ['Org:Contestant', 'create'],
            ['Org:Contestant', 'list'],

            ['Org:Dashboard', 'default'],
            ['Org:Event', 'create'],
            ['Org:Event', 'list'],
            ['Org:StoredQuery', 'create'],
            ['Org:StoredQuery', 'list'],

            ['Org:Org', 'list'],
            ['Org:Org', 'create'],
            ['Org:Points', 'entry'],
            ['Org:Points', 'preview'],
            ['Org:Tasks', 'import'],
            ['Org:Teacher', 'list'],
            ['Org:Teacher', 'create'],

            ['Org:Chart', 'list'],
            ['Org:Chart', 'contestantsPerSeries'],
            ['Org:Chart', 'totalContestantsPerSeries'],
            ['Org:Chart', 'contestantsPerYears'],
            ['Org:Chart', 'totalPersons'],

            ['Org:Deduplicate', 'person'],
            ['Org:Person', 'create'],
            ['Org:Person', 'edit'],
            ['Org:Person', 'detail'],
            ['Org:Person', 'pizza'],
            ['Org:Person', 'search'],
            ['Org:School', 'list'],
            ['Org:School', 'create'],
            ['Org:Spam', 'list'],
            ['Org:Validation', 'default'],
            ['Org:Validation', 'list'],
            ['Org:Validation', 'preview'],

            ['Warehouse:Dashboard', 'default'],

            //['Warehouse:Item', 'create'],
            ['Warehouse:Item', 'list'],

            ['Warehouse:Producer', 'list'],

            ['Warehouse:Product', 'list'],
        ];
    }

    protected function tearDown(): void
    {
        $this->truncateTables(['person_info', 'org']);
        parent::tearDown();
    }
}

$testCase = new OrgModule($container);
$testCase->run();
