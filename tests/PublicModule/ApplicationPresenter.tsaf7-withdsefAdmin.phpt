<?php

namespace FKSDB\Tests\PublicModule;

$container = require '../bootstrap.php';

use Nette\Application\Responses\RedirectResponse;
use Tester\Assert;

class ApplicationPresenterTest extends ApplicationPresenterTsafTestCase {
    // /** @var int */
    // private $tsafAppId;
    /** @var int */
    private $dsefAppId;

    protected function setUp() {
        parent::setUp();
        $adminId = $this->createPerson('Admin', 'Adminovič', [], true);
        $this->insert('grant', [
            'login_id' => $adminId,
            'role_id' => 5,
            'contest_id' => 1,
        ]);
        $this->authenticate($adminId);

        $this->dsefAppId = $this->insert('event_participant', [
            'person_id' => $this->personId,
            'event_id' => $this->dsefEventId,
            'status' => 'applied',
        ]);

        $this->insert('e_dsef_participant', [
            'event_participant_id' => $this->dsefAppId,
            'e_dsef_group_id' => 1,
            'lunch_count' => 3,
        ]);
    }

    public function testRegistration() {
        $request = $this->createPostRequest([
            'participantTsaf' => [
                'person_id' => $this->personId,
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
                        'phone' => '+420987654321',
                    ],
                    'post_contact_d' => [
                        'address' => [
                            'target' => "jkljhkjh",
                            'city' => "jkhlkjh",
                            'postal_code' => "64546",
                            'country_iso' => "",
                        ],
                    ],
                ],
                'tshirt_size' => 'F_S',
                'jumper_size' => 'F_M',
            ],
            'participantDsef' => [
                'e_dsef_group_id' => "1",
                'lunch_count' => "3",
                'message' => "",
            ],
            'privacy' => "on",
            'c_a_p_t_cha' => "pqrt",
            '__init__applied' => "Přihlásit účastníka",
        ], [
            'eventId' => $this->tsafEventId,
        ]);

        $response = $this->fixture->run($request);

        Assert::type(RedirectResponse::class, $response);

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
