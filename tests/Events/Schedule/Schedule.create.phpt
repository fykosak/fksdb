<?php

namespace Events\Accommodation;

use Tester\Assert;

$container = require '../../bootstrap.php';

class ScheduleTest extends ScheduleTestCase {

    public function testRegistration() {
        $request = $this->createAccommodationRequest();

        $response = $this->fixture->run($request);
        Assert::type('Nette\Application\Responses\RedirectResponse', $response);

        Assert::equal('3', $this->connection->fetchColumn('SELECT count(*) FROM person_schedule WHERE schedule_item_id = ?', $this->itemId));
    }

    public function getAccommodationCapacity() {
        return 3;
    }

}


$testCase = new ScheduleTest($container);
$testCase->run();
