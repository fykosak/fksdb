<?php

namespace FKSDB\Tests\PresentersTests\PageDisplay;

$container = require '../../bootstrap.php';

/**
 * Class OrgModule
 * @author Michal Červeňák <miso@fykos.cz>
 */
class CoreModule extends AbstractPageDisplayTestCase {
    public function getPages(): array {
        return [
            ['Core:Dispatch', 'default'],
            ['Core:MyPayments', 'default'],
            ['Core:Settings', 'default'],
        ];
    }
}

$testCase = new CoreModule($container);
$testCase->run();
