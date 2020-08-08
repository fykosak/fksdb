<?php

namespace FKSDB\Tests\Events\Schedule;

use FKSDB\ORM\DbNames;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Nette\DI\Config\Helpers;
use Nette\Utils\DateTime;
use Tester\Assert;

$container = require '../../bootstrap.php';

class DeleteTest extends ScheduleTestCase {
    /** @var int */
    protected $lastPersonId;
    /** @var int */
    protected $dsefAppId;
    /** @var int */
    private $lastPSId;

    public function setUp() {
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
        $this->lastPSId = $this->insert('person_schedule', [
            'person_id' => $this->lastPersonId,
            'schedule_item_id' => $this->itemId,
        ]);
        $loginId = $this->insert('login', ['person_id' => $this->lastPersonId, 'active' => 1]);
        $this->insert(DbNames::TAB_GRANT, ['login_id' => $loginId, 'role_id' => 5, 'contest_id' => 1]);
        $this->authenticate($loginId);

    }

    public function testRegistration() {
        $postData = [
            'participant' => [
                'person_id' => $this->lastPersonId,
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
                'e_dsef_group_id' => 2,
                'lunch_count' => 0,
                'message' => '',
            ],
            'privacy' => 'on',
            'c_a_p_t_cha' => 'pqrt',
            'cancelled____terminated' => 'Zrušit přihlášku',
        ];

        $post = Helpers::merge([], [
            'action' => 'default',
            'lang' => 'cs',
            'contestId' => 1,
            'year' => 1,
            'eventId' => $this->eventId,
            'id' => $this->dsefAppId,
            'do' => 'application-form-form-submit',
        ]);
        $request = new Request('Public:Application', 'POST', $post, $postData);

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
