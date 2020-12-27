<?php

namespace FKSDB\Tests\Events\Schedule;

use FKSDB\Models\ORM\DbNames;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Nette\Schema\Helpers;
use Nette\Utils\DateTime;
use Tester\Assert;

$container = require '../../Bootstrap.php';

class DeleteTest extends ScheduleTestCase {

    protected int $lastPersonId;

    protected int $dsefAppId;

    protected function setUp(): void {
        parent::setUp();
        $this->lastPersonId = $this->createPerson('Paní', 'Bílá III.',
            [
                'email' => 'bila3-acc@hrad.cz',
                'born' => DateTime::from('2000-01-01'),
            ]);
        $this->dsefAppId = $this->insert('event_participant', [
            'person_id' => $this->lastPersonId,
            'event_id' => $this->eventId,
            'status' => 'cancelled',
        ]);
        $this->insert(DbNames::TAB_E_DSEF_PARTICIPANT,
            [
                'event_participant_id' => $this->dsefAppId,
                'e_dsef_group_id' => 2,
            ]);
        $this->insert('person_schedule', [
            'person_id' => $this->lastPersonId,
            'schedule_item_id' => $this->itemId,
        ]);
        $loginId = $this->insert('login', ['person_id' => $this->lastPersonId, 'active' => 1]);
        $this->insert(DbNames::TAB_GRANT, ['login_id' => $loginId, 'role_id' => 5, 'contest_id' => 1]);
        $this->authenticate($loginId, $this->fixture);
    }

    public function testRegistration(): void {
        $formData = [
            'participant' => [
                'person_id' => (string)$this->lastPersonId,
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
                        'accommodation' => json_encode([$this->groupId => $this->itemId]),
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
            'eventId' => (string)$this->eventId,
            'id' => (string)$this->dsefAppId,
        ]);
        $request = new Request('Public:Application', 'POST', $params, $formData);

        $response = $this->fixture->run($request);
        Assert::type(RedirectResponse::class, $response);

        //Assert::equal('cancelled', $this->connection->fetchField('SELECT status FROM event_participant WHERE event_participant_id=?', $this->dsefAppId));
        Assert::equal(0, (int)$this->connection->fetchField('SELECT count(*) FROM person_schedule WHERE schedule_item_id = ? AND person_id=?', $this->itemId, $this->lastPersonId));
    }

    public function getAccommodationCapacity(): int {
        return 3;
    }
}

$testCase = new DeleteTest($container);
$testCase->run();
