<?php

namespace FKSDB\Tests\Events\Schedule;

use Nette\Application\Responses\TextResponse;
use Tester\Assert;

$container = require '../../Bootstrap.php';

class LimitTest extends ScheduleTestCase {

    public function testRegistration(): void {
        Assert::equal(2, (int)$this->explorer->fetchField('SELECT count(*) FROM person_schedule WHERE schedule_item_id = ?', $this->itemId));

        $request = $this->createAccommodationRequest();
        $response = $this->fixture->run($request);
        Assert::type(TextResponse::class, $response);
        Assert::equal(2, (int)$this->explorer->fetchField('SELECT count(*) FROM person_schedule WHERE schedule_item_id = ?', $this->itemId));
    }

    public function getAccommodationCapacity(): int {
        return 2;
    }
}

$testCase = new LimitTest($container);
$testCase->run();
