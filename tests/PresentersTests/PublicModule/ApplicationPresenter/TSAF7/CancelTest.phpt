<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter\TSAF7;

$container = require '../../../../Bootstrap.php';

use FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter\TsafTestCase;
use Nette\Application\Responses\RedirectResponse;
use Tester\Assert;

class CancelTest extends TsafTestCase
{

    private int $tsafAppId;

    protected function setUp(): void
    {
        parent::setUp();

        $adminId = $this->createPerson('Admin', 'Adminovič', [], []);
        $this->insert('grant', [
            'login_id' => $adminId,
            'role_id' => 5,
            'contest_id' => 1,
        ]);
        $this->authenticate($adminId, $this->fixture);

        $this->tsafAppId = $this->insert('event_participant', [
            'person_id' => $this->personId,
            'event_id' => $this->tsafEventId,
            'status' => 'applied',
        ]);

        $dsefAppId = $this->insert('event_participant', [
            'person_id' => $this->personId,
            'event_id' => $this->dsefEventId,
            'status' => 'applied.tsaf',
            'lunch_count' => 3,
        ]);

        $this->insert('e_dsef_participant', [
            'event_participant_id' => $dsefAppId,
            'e_dsef_group_id' => 1,
        ]);
    }

    public function testCancel(): void
    {
        $request = $this->createPostRequest([
            'participantTsaf' => [
                'person_id' => (string)$this->personId,
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
            'auto_invited_or_invited_or_applied_or_applied_nodsef__cancelled' => "Zrušit přihlášku",
        ], [
            'eventId' => (string)$this->tsafEventId,
            'id' => (string)$this->tsafAppId,
        ]);

        $response = $this->fixture->run($request);

        Assert::type(RedirectResponse::class, $response);

        $application = $this->assertApplication($this->tsafEventId, 'bila@hrad.cz');
        Assert::equal('cancelled', $application->status);
        Assert::equal('F_S', $application->tshirt_size);
        Assert::equal('F_M', $application->jumper_size);

        $application = $this->assertApplication($this->dsefEventId, 'bila@hrad.cz');
        Assert::equal('applied.notsaf', $application->status);

        $eApplication = $this->assertExtendedApplication($application, 'e_dsef_participant');
        Assert::equal(1, $eApplication->e_dsef_group_id);
        Assert::equal(3, $application->lunch_count);
    }
}

$testCase = new CancelTest($container);
$testCase->run();
