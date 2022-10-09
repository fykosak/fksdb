<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PageDisplay;

use FKSDB\Models\ORM\Services\ContestantService;

// phpcs:disable
$container = require '../../Bootstrap.php';

// phpcs:enable
class PublicModule extends AbstractPageDisplayTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->getContainer()->getByType(ContestantService::class)->storeModel(
            ['person_id' => $this->person->person_id, 'contest_id' => 1, 'year' => 1]
        );
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
}
// phpcs:disable
$testCase = new PublicModule($container);
$testCase->run();
// phpcs:enable
