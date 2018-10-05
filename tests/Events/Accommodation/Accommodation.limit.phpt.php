<?php

namespace Events\Accommodation;

use Tester\Assert;

$container = require '../../bootstrap.php';

class AccommodationTest extends AccommodationTestCase {


    public function testRegistration() {
        $request = $this->createPostRequest([
            'participant' => [
                'person_id' => "__promise",
                'person_id_1' => [
                    '_c_compact' => " ",
                    'person' => [
                        'other_name' => "František",
                        'family_name' => "Dobrota",
                    ],
                    'person_info' => [
                        'email' => "ksaadaa@kalo.cz",
                        'id_number' => "1231354",
                        'born' => "15. 09. 2014",
                    ],
                    'post_contact_p' => [
                        'address' => [
                            'target' => "jkljhkjh",
                            'city' => "jkhlkjh",
                            'postal_code' => "64546",
                            'country_iso' => "",
                        ],
                    ],
                    'person_accommodation' => [
                        'matrix' => json_encode(['2018-06-05' => $this->accId]),
                    ],
                ],
                'e_dsef_group_id' => 2,
                'lunch_count' => 0,
                'message' => "",
            ],
            'privacy' => "on",
            'c_a_p_t_cha' => "pqrt",
            '__init__applied' => "Přihlásit účastníka",
        ]);

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
