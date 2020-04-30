<?php

$container = require '../bootstrap.php';

use FKSDB\Components\Forms\Controls\ReferencedId;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\ITemplate;
use Nette\Utils\DateTime;
use Tester\Assert;

class ApplicationPresenterTest extends ApplicationPresenterDsefTestCase {

    public function testRegistration() {
        //Assert::equal(false, $this->fixture->getUser()->isLoggedIn()); (presnter not ready for redirect)

        $request = $this->createPostRequest([
            'participant' => [
                'person_id' => ReferencedId::VALUE_PROMISE,
                'person_id_1' => [
                    '_c_compact' => " ",
                    'person' => [
                        'other_name' => "Paní",
                        'family_name' => "Bílá",
                    ],
                    'person_info' => [
                        'email' => "bila@hrad.cz",
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
                'e_dsef_group_id' => "1",
                'lunch_count' => "3",
                'message' => "",
            ],
            'privacy' => "on",
            'c_a_p_t_cha' => "pqrt",
            '__init__applied' => "Přihlásit účastníka",
        ]);

        $response = $this->fixture->run($request);
        Assert::type(TextResponse::class, $response);

        $source = $response->getSource();
        Assert::type(ITemplate::class, $source);

        $html = (string)$source;
        Assert::contains('<div
            id="frm-application-form-form-participant-person_id_1-person_info-born-pair" class="form-group has-error">', $html);

        $info = $this->assertPersonInfo($this->personId);
        Assert::equal(null, $info->id_number); // shouldn't be rewritten
        Assert::equal(DateTime::from('2000-01-01'), $info->born); // shouldn't be rewritten
    }

}

$testCase = new ApplicationPresenterTest($container);
$testCase->run();
