<?php

declare(strict_types=1);

namespace FKSDB\Tests\ModelsTests\Schedule;

// phpcs:disable
$container = require '../../Bootstrap.php';

// phpcs:enable
use FKSDB\Components\Forms\Controls\Schedule\Handler;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;

class HandlerTest extends DatabaseTestCase
{
    private Handler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new Handler($this->container);
    }

    public function testSave(): void
    {

    }
}

$testCase = new HandlerTest($container);
$testCase->run();
