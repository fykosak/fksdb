<?php

namespace FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter\DSEF20;

$container = require '../../../../Bootstrap.php';

use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter\DsefTestCase;
use Nette\Application\Responses\RedirectResponse;
use Nette\Utils\DateTime;
use Tester\Assert;

class AnonymousMatchTest extends DsefTestCase {

    protected function tearDown(): void {
         $this->truncateTables([DbNames::TAB_E_DSEF_PARTICIPANT]);
        parent::tearDown();
    }

    public function testRegistration(): void {
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
                        'born' => "2000-01-01",
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
        Assert::type(RedirectResponse::class, $response);

        $application = $this->assertApplication($this->eventId, 'bila@hrad.cz');
        Assert::equal('applied', $application->status);
        Assert::equal((int)$this->personId, $application->person_id);

        $info = $this->assertPersonInfo($this->personId);
        Assert::equal('1231354', $info->id_number);
        Assert::equal(DateTime::from('2000-01-01'), $info->born);


        $eApplication = $this->assertExtendedApplication($application, 'e_dsef_participant');
        Assert::equal(1, $eApplication->e_dsef_group_id);
        Assert::equal(3, $application->lunch_count);
    }

}

$testCase = new AnonymousMatchTest($container);
$testCase->run();
