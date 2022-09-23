<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PageDisplay;

// phpcs:disable
$container = require '../../Bootstrap.php';

// phpcs:enable
class CoreModule extends AbstractPageDisplayTestCase
{
    public function getPages(): array
    {
        return [
            ['Core:MyApplications', 'default'],
            // ['Core:Dispatch', 'default'], disable cause of fast links
            ['Core:MyPayments', 'default'],
            ['Core:Settings', 'default'],
        ];
    }
}
// phpcs:disable
$testCase = new CoreModule($container);
$testCase->run();
// phpcs:enable
