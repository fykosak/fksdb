<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter\TSAF7;

$container = require '../../../../Bootstrap.php';

use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter\TsafTestCase;
use Nette\Application\Responses\RedirectResponse;
use Tester\Assert;

class NoDSEFTest extends TsafTestCase
{

    protected EventParticipantModel $tsafApp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authenticatePerson($this->person, $this->fixture);

        $this->tsafApp = $this->getContainer()->getByType(EventParticipantService::class)->storeModel([
            'person_id' => $this->person,
            'event_id' => $this->tsafEvent->event_id,
            'status' => 'invited',
        ]);
    }

    public function testRegistration(): void
    {
        $request = $this->createPostRequest([
            'participantTsaf' => [
                'person_id' => (string)$this->person->person_id,
                'person_id_1' => [
                    '_c_compact' => ' ',
                    'person' => [
                        'other_name' => 'Paní',
                        'family_name' => 'Bílá',
                    ],
                    'person_info' => [
                        'email' => 'bila@hrad.cz',
                        'id_number' => '1231354',
                        'born' => '2014-09-15',
                        'phone' => '+420987654321',
                    ],
                    'post_contact_d' => [
                        'address' => [
                            'target' => 'jkljhkjh',
                            'city' => 'jkhlkjh',
                            'postal_code' => '64546',
                            'country_iso' => '',
                        ],
                    ],
                ],
                'tshirt_size' => 'F_S',
                'jumper_size' => 'F_M',
            ],
            'participantDsef' => [
                'e_dsef_group_id' => '1',
                'lunch_count' => '3',
                'message' => '',
            ],
            'privacy' => 'on',
            'c_a_p_t_cha' => 'pqrt',
            'invited__applied' => 'Potvrdit účast',
        ], [
            'eventId' => $this->tsafEvent->event_id,
            'id' => $this->tsafApp->event_participant_id,
        ]);
        /** @var EmailMessageService $serviceEmail */
        $serviceEmail = $this->getContainer()->getByType(EmailMessageService::class);
        $before = $serviceEmail->getTable()->count();
        $response = $this->fixture->run($request);

        Assert::type(RedirectResponse::class, $response);

        $application = $this->assertApplication($this->tsafEvent, 'bila@hrad.cz');
        Assert::equal('applied', $application->status);
        Assert::equal('F_S', $application->tshirt_size);
        Assert::equal('F_M', $application->jumper_size);

        $application = $this->assertApplication($this->dsefEvent, 'bila@hrad.cz');
        Assert::equal('applied.tsaf', $application->status);

        $eApplication = $this->assertExtendedApplication($application, 'e_dsef_participant');
        Assert::equal(1, $eApplication->e_dsef_group_id);
        Assert::equal(3, $application->lunch_count);

        Assert::equal($before + 2, $serviceEmail->getTable()->count());
    }
}

$testCase = new NoDSEFTest($container);
$testCase->run();
