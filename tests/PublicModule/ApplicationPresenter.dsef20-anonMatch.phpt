<?php

$container = require '../bootstrap.php';

use FKS\Components\Forms\Controls\ReferencedId;
use Nette\DateTime;
use Tester\Assert;

class ApplicationPresenterTest extends ApplicationPresenterDsefTestCase {

    protected function tearDown() {
        $this->connection->query("DELETE FROM e_dsef_participant");
        parent::tearDown();
    }

    public function testRegistration() {
        //Assert::equal(false, $this->fixture->getUser()->isLoggedIn()); (presnter not ready for redirect)

        $request = $this->createPostRequest(array(
            'participant' => array(
                'person_id' => ReferencedId::VALUE_PROMISE,
                'person_id_1' => array(
                    '_c_compact' => " ",
                    'person' => array(
                        'other_name' => "Paní",
                        'family_name' => "Bílá",
                    ),
                    'person_info' => array(
                        'email' => "bila@hrad.cz",
                        'id_number' => "1231354",
                        'born' => "1. 1. 2000",
                    ),
                    'post_contact_p' => array(
                        'address' => array(
                            'target' => "jkljhkjh",
                            'city' => "jkhlkjh",
                            'postal_code' => "64546",
                            'country_iso' => "",
                        ),
                    ),
                ),
                'e_dsef_group_id' => "1",
                'lunch_count' => "3",
                'message' => "",
            ),
            'privacy' => "on",
            'c_a_p_t_cha' => "pqrt",
            '__init__applied' => "Přihlásit účastníka",
        ));

        $response = $this->fixture->run($request);
        Assert::type('Nette\Application\Responses\RedirectResponse', $response);

        $application = $this->assertApplication($this->eventId, 'bila@hrad.cz');
        Assert::equal('applied', $application->status);
        Assert::equal((int) $this->personId, $application->person_id);

        $info = $this->assertPersonInfo($this->personId);
        Assert::equal('1231354', $info->id_number);
        Assert::equal(DateTime::from('2000-01-01'), $info->born);


        $eApplication = $this->assertExtendedApplication($application, 'e_dsef_participant');
        Assert::equal(1, $eApplication->e_dsef_group_id);
        Assert::equal(3, $eApplication->lunch_count);
    }

}

$testCase = new ApplicationPresenterTest($container);
$testCase->run();
