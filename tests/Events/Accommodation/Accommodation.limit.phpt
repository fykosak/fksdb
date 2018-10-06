<?php

namespace Events\Accommodation;

use Tester\Assert;

$container = require '../../bootstrap.php';

class AccommodationTest extends AccommodationTestCase {


    public function testRegistration() {
        Assert::equal('2', $this->connection->fetchColumn('SELECT count(*) FROM event_person_accommodation WHERE event_accommodation_id = ?', $this->accId));

        $request = $this->createAccommodationRequest();
        $response = $this->fixture->run($request);
        Assert::type('Nette\Application\Responses\TextResponse', $response);
      //  file_put_contents('./acc_out.html', $response->getSource());
        Assert::equal('2', $this->connection->fetchColumn('SELECT count(*) FROM event_person_accommodation WHERE event_accommodation_id = ?', $this->accId));
    }

    public function getAccommodationCapacity() {
        return 2;
    }
}


$testCase = new AccommodationTest($container);
$testCase->run();
