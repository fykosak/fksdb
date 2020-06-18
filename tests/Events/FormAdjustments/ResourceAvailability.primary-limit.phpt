<?php

namespace FKSDB\Events\Model;

use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\ITemplate;
use Tester\Assert;
use Tester\DomQuery;

$container = require '../../bootstrap.php';

class ResourceAvailabilityTest extends ResourceAvailabilityTestCase {

    public function testDisplay() {
        $request = new Request('Public:Application', 'GET', [
            'action' => 'default',
            'lang' => 'cs',
            'contestId' => 1,
            'year' => 1,
            'eventId' => $this->eventId,
        ]);

        $response = $this->fixture->run($request);
        Assert::type(TextResponse::class, $response);

        $source = $response->getSource();
        Assert::type(\Nette\Application\UI\ITemplate::class, $source);

        $html = (string) $source;
        $dom = DomQuery::fromHtml($html);
        Assert::true((bool) $dom->xpath('//input[@name="participant[accomodation]"][@disabled="disabled"]'));
    }

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
                        'email' => "ksaad@kalo.cz",
                        'id_number' => "1231354",
                        'born' => "2014-09-15",
                    ],
                    'post_contact_p' => [
                        'address' => [
                            'target' => "jkljhkjh",
                            'city' => "jkhlkjh",
                            'postal_code' => "64546",
                            'country_iso' => "",
                        ],
                    ],
                ],
                'accomodation' => "1",
                'e_dsef_group_id' => "1",
                'lunch_count' => "3",
                'message' => "",
            ],
            'privacy' => "on",
            'c_a_p_t_cha' => "pqrt",
            '__init__applied' => "Přihlásit účastníka",
        ]);

        $response = $this->fixture->run($request);
        Assert::type(RedirectResponse::class, $response);

        Assert::equal(2, (int)$this->connection->fetchField('SELECT SUM(accomodation) FROM event_participant WHERE event_id = ?', $this->eventId));
    }

    public function getCapacity() {
        return 2;
    }

}

$testCase = new ResourceAvailabilityTest($container);
$testCase->run();
