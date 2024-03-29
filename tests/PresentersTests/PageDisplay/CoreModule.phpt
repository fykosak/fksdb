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
            ['Core:Dispatch', 'default'],// disable cause of fast links
            ['Core:Settings', 'default'],
        ];
    }
}
// phpcs:disable
$testCase = new CoreModule($container);
$testCase->run();
// phpcs:enable
