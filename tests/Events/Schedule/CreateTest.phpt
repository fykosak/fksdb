<?php

namespace FKSDB\Tests\Events\Schedule;

use Nette\Application\Responses\RedirectResponse;
use Tester\Assert;

$container = require '../../bootstrap.php';

class CreateTest extends ScheduleTestCase {

    public function testRegistration() {
        $request = $this->createAccommodationRequest();

        $response = $this->fixture->run($request);
        Assert::type(RedirectResponse::class, $response);

        Assert::equal(3, (int)$this->connection->fetchField('SELECT count(*) FROM person_schedule WHERE schedule_item_id = ?', $this->itemId));
    }

    public function getAccommodationCapacity(): int {
        return 3;
    }
}

$testCase = new CreateTest($container);
$testCase->run();
