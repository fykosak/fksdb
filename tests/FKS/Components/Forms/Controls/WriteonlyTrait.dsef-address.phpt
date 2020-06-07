<?php

$container = require '../../../../bootstrap.php';

use FKSDB\ORM\Models\ModelPostContact;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\ITemplate;
use Nette\Utils\DateTime;
use Tester\Assert;

class WriteonlyTraitTest extends ApplicationPresenterDsefTestCase {

    private $dsefAppId;

    protected function setUp() {
        parent::setUp();

        // create address for person
        $addressId = $this->insert('address', [
            'target' => 'PomaláUlice',
            'city' => 'SinCity',
            'postal_code' => '67401',
            'region_id' => '3',
        ]);
        $this->insert('post_contact', [
            'person_id' => $this->personId,
            'address_id' => $addressId,
            'type' => ModelPostContact::TYPE_DELIVERY,
        ]);

        // apply person
        $this->dsefAppId = $this->insert('event_participant', [
            'person_id' => $this->personId,
            'event_id' => $this->eventId,
            'status' => 'applied'
        ]);

        $this->insert('e_dsef_participant', [
            'event_participant_id' => $this->dsefAppId,
            'e_dsef_group_id' => 1,
            'lunch_count' => 3,
        ]);

        // create admin
        $adminId = $this->createPerson('Admin', 'Adminovič', [], true);
        $this->insert('grant', [
            'login_id' => $adminId,
            'role_id' => 5,
            'contest_id' => 1,
        ]);
        $this->authenticate($adminId);
    }

    public function testDisplay() {
        Assert::equal(true, $this->fixture->getUser()->isLoggedIn());

        $request = new Request('Public:Application', 'GET', [
            'action' => 'default',
            'lang' => 'cs',
            'contestId' => 1,
            'year' => 1,
            'eventId' => $this->eventId,
            'id' => $this->dsefAppId,
        ]);

        $response = $this->fixture->run($request);
        Assert::type(TextResponse::class, $response);

        $source = $response->getSource();
        Assert::type(\Nette\Application\UI\ITemplate::class, $source);

        $html = (string)$source;
        Assert::contains('Účastník', $html);

        Assert::contains('Paní Bílá', $html);

        Assert::notContains('PomaláUlice', $html);
        Assert::notContains('SinCity', $html);
    }

    public function testSave() {
        Assert::equal(true, $this->fixture->getUser()->isLoggedIn());

        $request = $this->createPostRequest([
            'participant' =>
                [
                    'person_id' => $this->personId,
                    'person_id_1' =>
                        [
                            '_c_compact' => 'Paní Bílá',
                            'person' =>
                                [
                                    'other_name' => 'Paní',
                                    'family_name' => 'Bílá',
                                ],
                            'person_info' =>
                                [
                                    'email' => 'bila@hrad.cz',
                                    'id_number' => '__original',
                                    'born' => '__original',
                                ],
                            'post_contact_p' =>
                                [
                                    'address' =>
                                        [
                                            'target' => '__original',
                                            'city' => '__original',
                                            'postal_code' => '67401',
                                            'country_iso' => 'CZ',
                                        ],
                                ],
                        ],
                    'e_dsef_group_id' => '1',
                    'lunch_count' => '3',
                    'message' => '',
                ],
            'save' => 'Uložit',
        ], [
            'id' => $this->dsefAppId,
        ]);

        $response = $this->fixture->run($request);

        //Assert::same('fsafs', (string) $response->getSource());
        Assert::type(RedirectResponse::class, $response);


        $application = $this->assertApplication($this->eventId, 'bila@hrad.cz');
        Assert::equal('applied', $application->status);
        Assert::equal((int)$this->personId, $application->person_id);

        $info = $this->assertPersonInfo($this->personId);
        Assert::equal(null, $info->id_number);
        Assert::equal(DateTime::from('2000-01-01'), $info->born);


        $eApplication = $this->assertExtendedApplication($application, 'e_dsef_participant');
        Assert::equal(1, $eApplication->e_dsef_group_id);
        Assert::equal(3, $eApplication->lunch_count);

        $addressId = $this->connection->fetchField('SELECT address_id FROM post_contact WHERE person_id = ? AND type = ?', $this->personId, ModelPostContact::TYPE_PERMANENT);
        Assert::notEqual(false, $addressId);
        $address = $this->connection->fetch('SELECT * FROM address WHERE address_id = ?', $addressId);
        Assert::notEqual(false, $address);

        Assert::equal('PomaláUlice', $address->target);
        Assert::equal('SinCity', $address->city);
        Assert::equal('67401', $address->postal_code);
    }

}

$testCase = new WriteonlyTraitTest($container);
$testCase->run();
