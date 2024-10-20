<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PageDisplay;

// phpcs:disable
$container = require '../../Bootstrap.php';

// phpcs:enable
class ProfileModule extends AbstractPageDisplayTestCase
{
    public function getPages(): array
    {
        return [
            ['Profile:Dashboard', 'default'],
            ['Profile:Email', 'default'],
            ['Profile:Email', 'confirm'],
            ['Profile:Lang', 'default'],
            ['Profile:Login', 'default'],
            ['Profile:MyApplications', 'default'],
            ['Shop:MyPayments', 'default'],
            ['Profile:PostContact', 'default'],
        ];
    }
}

// phpcs:disable
$testCase = new ProfileModule($container);
$testCase->run();
// phpcs:enable
