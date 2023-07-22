<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PageDisplay;

use FKSDB\Models\ORM\Services\OrgService;
use FKSDB\Models\ORM\Services\PersonInfoService;

// phpcs:disable
$container = require '../../Bootstrap.php';

// phpcs:enable
class OrgModule extends AbstractPageDisplayTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->container->getByType(OrgService::class)->storeModel([
            'person_id' => $this->person->person_id,
            'contest_id' => 1,
            'since' => 1,
            'order' => 1,
        ]);
        $this->container->getByType(PersonInfoService::class)->storeModel(
            ['person_id' => $this->person->person_id]
        );
    }

    protected function transformParams(string $presenterName, string $action, array $params): array
    {
        [$presenterName, $action, $params] = parent::transformParams($presenterName, $action, $params);
        $params['year'] = "1";
        $params['contestId'] = "1";
        $params['series'] = "1";
        if ($presenterName === 'Org:Person') {
            $params['id'] = (string)$this->person->person_id;
        }
        return [$presenterName, $action, $params];
    }

    public function getPages(): array
    {
        return [
            ['Org:Inbox', 'corrected'],

            ['Org:Contestant', 'list'],

            ['Org:Tasks', 'dispatch'],
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
}

// phpcs:disable
$testCase = new OrgModule($container);
$testCase->run();
// phpcs:enable
