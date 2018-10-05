<?php

namespace Events\Accommodation;

use Tester\Assert;

$container = require '../../bootstrap.php';

class AccommodationTest extends AccommodationTestCase {


    public function testRegistration() {
        $request = $this->createAccommodationRequest();
        $response = $this->fixture->run($request);
        Assert::type('Nette\Application\Responses\TextResponse', $response);

        Assert::equal('2', $this->connection->fetchColumn('SELECT count(*) FROM event_person_accommodation WHERE event_accommodation_id = ?', $this->accId));
    }

    public function getAccommodationCapacity() {
        return 2;
    }
}


$testCase = new AccommodationTest($container);
$testCase->run();
