<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PageDisplay;

use FKSDB\Models\ORM\Services\OrganizerService;
use FKSDB\Models\ORM\Services\PersonInfoService;

// phpcs:disable
$container = require '../../Bootstrap.php';

// phpcs:enable
class OrganizerModule extends AbstractPageDisplayTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->container->getByType(OrganizerService::class)->storeModel([
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
        if ($presenterName === 'Organizer:Person') {
            $params['id'] = (string)$this->person->person_id;
        }
        return [$presenterName, $action, $params];
    }

    public function getPages(): array
    {
        return [
            ['Organizer:Inbox', 'corrected'],

            ['Organizer:Contestant', 'list'],

            ['Organizer:Tasks', 'dispatch'],
            ['Organizer:Inbox', 'inbox'],
            ['Organizer:Inbox', 'list'],

            ['Organizer:Contestant', 'create'],
            ['Organizer:Contestant', 'list'],

            ['Organizer:Dashboard', 'default'],
            ['Organizer:Event', 'create'],
            ['Organizer:Event', 'list'],
            ['Organizer:StoredQuery', 'create'],
            ['Organizer:StoredQuery', 'list'],

            ['Organizer:Organizer', 'list'],
            ['Organizer:Organizer', 'create'],
            ['Organizer:Points', 'entry'],
            ['Organizer:Points', 'preview'],
            ['Organizer:Tasks', 'import'],
            ['Organizer:Teacher', 'list'],
            ['Organizer:Teacher', 'create'],

            ['Organizer:Chart', 'list'],

            ['Organizer:Deduplicate', 'person'],

            ['Organizer:Person', 'create'],
            ['Organizer:Person', 'edit'],
            ['Organizer:Person', 'detail'],
            ['Organizer:Person', 'pizza'],
            ['Organizer:Person', 'search'],
            ['Organizer:Person', 'tests'],
            ['Organizer:Person', 'list'],

            ['Organizer:Schools', 'default'],
            ['Organizer:Schools', 'create'],
            ['Organizer:Email', 'list'],

            ['Warehouse:Dashboard', 'default'],

            //['Warehouse:Item', 'create'],
            ['Warehouse:Item', 'list'],

            ['Warehouse:Producer', 'list'],

            ['Warehouse:Product', 'list'],

            ['Spam:Dashboard', 'default'],

            ['Spam:School', 'create'],
            ['Spam:School', 'list'],

            ['Spam:Person', 'create'],
            ['Spam:Person', 'list'],
            ['Spam:Person', 'import'],

            ['Spam:Mail', 'list'],
            ['Spam:Mail', 'import'],
        ];
    }
}

// phpcs:disable
$testCase = new OrganizerModule($container);
$testCase->run();
// phpcs:enable
