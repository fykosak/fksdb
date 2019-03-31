<?php

$container = require '../bootstrap.php';

use Tester\Assert;

class ApplicationPresenterTest extends ApplicationPresenterTsafTestCase {

    private $tsafAppId;
    private $dsefAppId;

    protected function setUp() {
        parent::setUp();
        $this->authenticate($this->personId);

        $this->tsafAppId = $this->insert('event_participant', array(
            'person_id' => $this->personId,
            'event_id' => $this->tsafEventId,
            'status' => 'invited'
        ));



        $this->insert('e_tsaf_participant', array(
            'event_participant_id' => $this->tsafAppId,
        ));

        $this->dsefAppId = $this->insert('event_participant', array(
            'person_id' => $this->personId,
            'event_id' => $this->dsefEventId,
            'status' => 'applied'
        ));

        $this->insert('e_dsef_participant', array(
            'event_participant_id' => $this->dsefAppId,
            'e_dsef_group_id' => 1,
            'lunch_count' => 3,
        ));
    }

    public function testRegistration() {
        $request = $this->createPostRequest(array(
            'participantTsaf' => array(
                'person_id' => $this->personId,
                'person_id_1' => array(
                    '_c_compact' => " ",
                    'person' => array(
                        'other_name' => "Paní",
                        'family_name' => "Bílá",
                    ),
                    'person_info' => array(
                        'email' => "bila@hrad.cz",
                        'id_number' => "1231354",
                        'born' => "15. 09. 2014",
                        'phone' => '+420987654321'
                    ),
                    'post_contact_d' => array(
                        'address' => array(
                            'target' => "jkljhkjh",
                            'city' => "jkhlkjh",
                            'postal_code' => "64546",
                            'country_iso' => null,
                        ),
                    ),
                ),
                'tshirt_size' => 'F_S',
                'jumper_size' => 'F_M',
            ),
            'participantDsef' => array(
                'e_dsef_group_id' => "1",
                'lunch_count' => "3",
                'message' => "",
            ),
            'privacy' => "on",
            'c_a_p_t_cha' => "pqrt",
            'invited__applied' => "Potvrdit účast",
                ), array(
            'eventId' => $this->tsafEventId,
            'id' => $this->tsafAppId,
        ));

        $response = $this->fixture->run($request);

        Assert::type('Nette\Application\Responses\RedirectResponse', $response);

        $application = $this->assertApplication($this->tsafEventId, 'bila@hrad.cz');
        Assert::equal('applied', $application->status);
        Assert::equal('F_S', $application->tshirt_size);

        $eApplication = $this->assertExtendedApplication($application, 'e_tsaf_participant');
        Assert::equal('F_M', $eApplication->jumper_size);

        $application = $this->assertApplication($this->dsefEventId, 'bila@hrad.cz');
        Assert::equal('applied.tsaf', $application->status);

        $eApplication = $this->assertExtendedApplication($application, 'e_dsef_participant');
        Assert::equal(1, $eApplication->e_dsef_group_id);
        Assert::equal(3, $eApplication->lunch_count);
    }

}

$testCase = new ApplicationPresenterTest($container);
$testCase->run();
