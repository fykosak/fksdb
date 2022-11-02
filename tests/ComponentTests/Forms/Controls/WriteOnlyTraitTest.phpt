<?php

declare(strict_types=1);

namespace FKSDB\Tests\ComponentTests\Forms\Controls;

// phpcs:disable
$container = require '../../../Bootstrap.php';

// phpcs:enable
use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnly;
use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnlyInput;
use FKSDB\Models\ORM\Models\AddressModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\PostContactType;
use FKSDB\Models\ORM\Services\CountryService;
use FKSDB\Models\ORM\Services\Events\ServiceDsefParticipant;
use FKSDB\Models\ORM\Services\AddressService;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\ORM\Services\GrantService;
use FKSDB\Models\ORM\Services\PostContactService;
use FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter\DsefTestCase;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Template;
use Nette\Utils\DateTime;
use Tester\Assert;

class WriteOnlyTraitTest extends DsefTestCase
{
    private EventParticipantModel $dsefApp;

    protected function setUp(): void
    {
        parent::setUp();

        // create address for person
        $address = $this->getContainer()->getByType(AddressService::class)->storeModel([
            'target' => 'PomaláUlice',
            'city' => 'SinCity',
            'postal_code' => '67401',
            'country_id' => (string)CountryService::SLOVAKIA,
        ]);
        $this->getContainer()->getByType(PostContactService::class)->storeModel([
            'person_id' => $this->person->person_id,
            'address_id' => $address->address_id,
            'type' => PostContactType::PERMANENT,
        ]);

        // apply person
        $this->dsefApp = $this->getContainer()->getByType(EventParticipantService::class)->storeModel([
            'person_id' => $this->person->person_id,
            'event_id' => $this->event->event_id,
            'status' => 'applied',
            'lunch_count' => 3,
        ]);

        $this->getContainer()->getByType(ServiceDsefParticipant::class)->storeModel([
            'event_participant_id' => $this->dsefApp->event_participant_id,
            'e_dsef_group_id' => 1,
        ]);

        // create admin
        $admin = $this->createPerson('Admin', 'Adminovič', null, []);
        $this->getContainer()->getByType(GrantService::class)->storeModel([
            'login_id' => $admin->person_id,
            'role_id' => 5,
            'contest_id' => 1,
        ]);
        $this->authenticatePerson($admin, $this->fixture);
    }

    public function testDisplay(): void
    {
        Assert::equal(true, $this->fixture->getUser()->isLoggedIn());

        $request = new Request('Public:Application', 'GET', [
            'action' => 'default',
            'lang' => 'cs',
            'contestId' => '1',
            'year' => '1',
            'eventId' => (string)$this->event->event_id,
            'id' => (string)$this->dsefApp->event_participant_id,
        ]);

        $response = $this->fixture->run($request);
        Assert::type(TextResponse::class, $response);
        /** @var TextResponse $response */
        $source = $response->getSource();
        Assert::type(Template::class, $source);

        $html = (string)$source;
        Assert::contains('Účastník', $html);

        Assert::contains('Paní Bílá', $html);

        Assert::notContains('PomaláUlice', $html);
        Assert::notContains('SinCity', $html);
    }

    public function testSave(): void
    {
        Assert::equal(true, $this->fixture->getUser()->isLoggedIn());

        $request = $this->createPostRequest([
            'participant' =>
                [
                    'person_id' => (string)$this->person->person_id,
                    'person_id_container' =>
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
                                    'id_number' => WriteOnly::VALUE_ORIGINAL,
                                    'born' => WriteOnly::VALUE_ORIGINAL,
                                ],
                            'post_contact_p' =>
                                [
                                    'address' =>
                                        [
                                            'target' => WriteOnly::VALUE_ORIGINAL,
                                            'city' => WriteOnly::VALUE_ORIGINAL,
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
            'id' => (string)$this->dsefApp->event_participant_id,
        ]);

        $response = $this->fixture->run($request);

        //Assert::same('fsafs', (string) $response->getSource());
        Assert::type(RedirectResponse::class, $response);

        $application = $this->assertApplication($this->event, 'bila@hrad.cz');
        Assert::equal('applied', $application->status->value);
        Assert::equal($this->person->person_id, $application->person_id);

        $info = $this->person->getInfo();
        Assert::equal(null, $info->id_number);
        Assert::equal(DateTime::from('2000-01-01'), $info->born);

        $eApplication = $this->assertExtendedApplication($application, 'e_dsef_participant');
        Assert::equal(1, $eApplication->e_dsef_group_id);
        Assert::equal(3, $application->lunch_count);

        $address = $this->getContainer()
            ->getByType(PostContactService::class)
            ->getTable()
            ->where(['person_id' => $this->person->person_id, 'type' => PostContactType::PERMANENT])
            ->fetch();

        Assert::notEqual(null, $address);
        /** @var AddressModel $address */
        $address = $this->getContainer()
            ->getByType(AddressService::class)
            ->getTable()
            ->where(['address_id' => $address->address_id])
            ->fetch();
        Assert::notEqual(null, $address);
        Assert::equal('PomaláUlice', $address->target);
        Assert::equal('SinCity', $address->city);
        Assert::equal('67401', $address->postal_code);
    }
}

// phpcs:disable
$testCase = new WriteOnlyTraitTest($container);
$testCase->run();
// phpcs:enable
