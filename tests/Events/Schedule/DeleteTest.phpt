<?php

declare(strict_types=1);

namespace FKSDB\Tests\Events\Schedule;

use FKSDB\Models\ORM\Models\ModelEventParticipant;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Services\Events\ServiceDsefParticipant;
use FKSDB\Models\ORM\Services\Schedule\ServicePersonSchedule;
use FKSDB\Models\ORM\Services\ServiceEventParticipant;
use FKSDB\Models\ORM\Services\ServiceGrant;
use FKSDB\Models\ORM\Services\ServiceLogin;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Nette\Schema\Helpers;
use Nette\Utils\DateTime;
use Tester\Assert;

$container = require '../../Bootstrap.php';

class DeleteTest extends ScheduleTestCase
{

    protected ModelPerson $lastPerson;

    protected ModelEventParticipant $dsefApp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lastPerson = $this->createPerson(
            'Paní',
            'Bílá III.',
            [
                'email' => 'bila3-acc@hrad.cz',
                'born' => DateTime::from('2000-01-01'),
            ]
        );
        $this->dsefApp = $this->getContainer()->getByType(ServiceEventParticipant::class)->createNewModel([
            'person_id' => $this->lastPerson->person_id,
            'event_id' => $this->event->event_id,
            'status' => 'cancelled',
        ]);
        $this->getContainer()->getByType(ServiceDsefParticipant::class)->createNewModel(
            [
                'event_participant_id' => $this->dsefApp->event_participant_id,
                'e_dsef_group_id' => 2,
            ]
        );
        $this->getContainer()->getByType(ServicePersonSchedule::class)->createNewModel([
            'person_id' => $this->lastPerson->person_id,
            'schedule_item_id' => $this->item->schedule_item_id,
        ]);
        $login = $this->getContainer()->getByType(ServiceLogin::class)->createNewModel(
            ['person_id' => $this->lastPerson->person_id, 'active' => 1]
        );
        $this->getContainer()->getByType(ServiceGrant::class)->createNewModel(
            ['login_id' => $login->login_id, 'role_id' => 5, 'contest_id' => 1]
        );
        $this->authenticateLogin($login, $this->fixture);
    }

    public function testRegistration(): void
    {
        $formData = [
            'participant' => [
                'person_id' => (string)$this->lastPerson->person_id,
                'person_id_1' => [
                    '_c_compact' => ' ',
                    'person' => [
                        'other_name' => 'Pani',
                        'family_name' => 'Bílá III.',
                    ],
                    'person_info' => [
                        'email' => 'bila3-acc@hrad.cz',
                        'id_number' => '1231354',
                        'born' => '2014-09-15',
                    ],
                    'post_contact_p' => [
                        'address' => [
                            'target' => 'jkljhkjh',
                            'city' => 'jkhlkjh',
                            'postal_code' => '64546',
                            'country_iso' => '',
                        ],
                    ],
                    'person_schedule' => [
                        'accommodation' => json_encode(
                            [$this->group->schedule_group_id => $this->item->schedule_item_id]
                        ),
                    ],
                ],
                'e_dsef_group_id' => (string)2,
                'lunch_count' => (string)0,
                'message' => '',
            ],
            'privacy' => 'on',
            'c_a_p_t_cha' => 'pqrt',
            'cancelled____terminated' => 'Zrušit přihlášku',
            '_do' => 'application-form-form-submit',
        ];

        $params = Helpers::merge([], [
            'action' => 'default',
            'lang' => 'cs',
            'contestId' => (string)1,
            'year' => (string)1,
            'eventId' => (string)$this->event->event_id,
            'id' => (string)$this->dsefApp->event_participant_id,
        ]);
        $request = new Request('Public:Application', 'POST', $params, $formData);

        $response = $this->fixture->run($request);
        Assert::type(RedirectResponse::class, $response);

        //Assert::equal('cancelled', $this->connection->fetchField('SELECT status FROM event_participant WHERE event_participant_id=?', $this->dsefAppId));
        Assert::equal(
            0,
            $this->getContainer()->getByType(ServicePersonSchedule::class)->getTable()->where(
                ['schedule_item_id' => $this->item->schedule_item_id, 'person_id' => $this->lastPerson->person_id]
            )->count('*')
        );
    }

    public function getAccommodationCapacity(): int
    {
        return 3;
    }
}

$testCase = new DeleteTest($container);
$testCase->run();
