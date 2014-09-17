<?php
$container = require '../bootstrap.php';

use Nette\Application\Request;
use Tester\Assert;

class ApplicationPresenterTest extends ApplicationPresenterDsefTestCase {


    public function testDisplay() {
        $request = new Request('Public:Application', 'GET', array(
            'action' => 'default',
            'lang' => 'cs',
            'contestId' => 1,
            'year' => 1,
            'eventId' => $this->eventId,
        ));

        $response = $this->fixture->run($request);
        Assert::type('Nette\Application\Responses\TextResponse', $response);

        $source = $response->getSource();
        Assert::type('Nette\Templating\ITemplate', $source);

        $html = (string) $source;
        Assert::contains('Účastník', $html);
    }

    public function testAnonymousRegistration() {
        $request = $this->createPostRequest(array(
            'participant' => array(
                'person_id' => "__promise",
                'person_id_1' => array(
                    '_c_compact' => " ",
                    'person' => array(
                        'other_name' => "František",
                        'family_name' => "Dobrota",
                    ),
                    'person_info' => array(
                        'email' => "ksaad@kalo.cz",
                        'id_number' => "1231354",
                        'born' => "15. 09. 2014",
                    ),
                    'post_contact' => array(
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

        $application = $this->assertApplication($this->eventId, 'ksaad@kalo.cz');
        Assert::equal('applied', $application->status);

        $eApplication = $this->assertExtendedApplication($application, 'e_dsef_participant');
        Assert::equal(1, $eApplication->e_dsef_group_id);
        Assert::equal(3, $eApplication->lunch_count);
    }

}

$testCase = new ApplicationPresenterTest($container);
$testCase->run();
