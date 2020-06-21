<?php

namespace FKSDB\Tests\PublicModule\ApplicationPresenter\ApplicationPresenter\TSAF7;

$container = require '../../../bootstrap.php';

use FKSDB\ORM\Services\ServiceEmailMessage;
use FKSDB\Tests\PublicModule\ApplicationPresenter\TsafTestCase;
use Nette\Application\Responses\RedirectResponse;
use Tester\Assert;

class NoDSEFTest extends TsafTestCase {
    /** @var int */
    private $tsafAppId;

    protected function setUp() {
        parent::setUp();
        $this->authenticate($this->personId);

        $this->tsafAppId = $this->insert('event_participant', [
            'person_id' => $this->personId,
            'event_id' => $this->tsafEventId,
            'status' => 'invited'
        ]);

        $this->insert('e_tsaf_participant', [
            'event_participant_id' => $this->tsafAppId,
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
                        'phone' => '+420987654321'
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
            'invited__applied' => "Potvrdit účast",
        ], [
            'eventId' => $this->tsafEventId,
            'id' => $this->tsafAppId,
        ]);
        /** @var ServiceEmailMessage $serviceEmail */
        $serviceEmail = $this->getContainer()->getByType(ServiceEmailMessage::class);
        $before = $serviceEmail->getTable()->count();
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

        Assert::equal($before + 2, $serviceEmail->getTable()->count());
    }

}

$testCase = new NoDSEFTest($container);
$testCase->run();
