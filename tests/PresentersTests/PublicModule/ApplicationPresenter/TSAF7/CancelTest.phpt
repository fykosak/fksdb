<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter\TSAF7;

$container = require '../../../../Bootstrap.php';

use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Services\Events\ServiceDsefParticipant;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\ORM\Services\GrantService;
use FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter\TsafTestCase;
use Nette\Application\Responses\RedirectResponse;
use Tester\Assert;

class CancelTest extends TsafTestCase
{

    private EventParticipantModel $tsafApp;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = $this->createPerson('Admin', 'Adminovič', null, []);
        $this->getContainer()->getByType(GrantService::class)->createNewModel([
            'login_id' => $admin->person_id,
            'role_id' => 5,
            'contest_id' => 1,
        ]);
        $this->authenticatePerson($admin, $this->fixture);

        $this->tsafApp = $this->getContainer()->getByType(EventParticipantService::class)->createNewModel([
            'person_id' => $this->person->person_id,
            'event_id' => $this->tsafEvent->event_id,
            'status' => 'applied',
        ]);

        $dsefApp = $this->getContainer()->getByType(EventParticipantService::class)->createNewModel([
            'person_id' => $this->person->person_id,
            'event_id' => $this->dsefEvent->event_id,
            'status' => 'applied.tsaf',
            'lunch_count' => 3,
        ]);

        $this->getContainer()->getByType(ServiceDsefParticipant::class)->createNewModel([
            'event_participant_id' => $dsefApp->event_participant_id,
            'e_dsef_group_id' => 1,
        ]);
    }

    public function testCancel(): void
    {
        $request = $this->createPostRequest([
            'participantTsaf' => [
                'person_id' => (string)$this->person->person_id,
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
            'eventId' => (string)$this->tsafEvent->event_id,
            'id' => (string)$this->tsafApp->event_participant_id,
        ]);

        $response = $this->fixture->run($request);

        Assert::type(RedirectResponse::class, $response);

        $application = $this->assertApplication($this->tsafEvent, 'bila@hrad.cz');
        Assert::equal('cancelled', $application->status);
        Assert::equal('F_S', $application->tshirt_size);
        Assert::equal('F_M', $application->jumper_size);

        $application = $this->assertApplication($this->dsefEvent, 'bila@hrad.cz');
        Assert::equal('applied.notsaf', $application->status);

        $eApplication = $this->assertExtendedApplication($application, 'e_dsef_participant');
        Assert::equal(1, $eApplication->e_dsef_group_id);
        Assert::equal(3, $application->lunch_count);
    }
}

$testCase = new CancelTest($container);
$testCase->run();
