<?php

namespace Persons;

$container = require '../bootstrap.php';

use BasePresenter;
use DatabaseTestCase;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelPerson;
use Nette\DI\Container;
use Nette\Forms\Form;
use Tester\Assert;

class ExtendedPersonHandlerTest extends DatabaseTestCase {

    /**
     * @var Container
     */
    private $container;

    /**
     * @var ExtendedPersonHandler
     */
    private $fixture;

    /**
     * @var ReferencedPersonFactory
     */
    private $referencedPersonFactory;

    function __construct(Container $container) {
        parent::__construct($container);
        $this->container = $container;
    }

    protected function setUp() {
        parent::setUp();

        $handlerFactory = $this->container->getByType('Persons\ExtendedPersonHandlerFactory');

        $service = $this->container->getService('ServiceContestant');
        $contest = $this->container->getService('ServiceContest')->findByPrimary(ModelContest::ID_FYKOS);
        $year = 1;
        $invitationLang = 'cs';
        $this->fixture = $handlerFactory->create($service, $contest, $year, $invitationLang);

        $this->referencedPersonFactory = $this->container->getByType('FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory');
    }

    protected function tearDown() {
        $this->connection->query("DELETE FROM contestant_base");
        $this->connection->query("DELETE FROM auth_token");
        $this->connection->query("DELETE FROM login");

        parent::tearDown();
    }

    public function testNewPerson() {
        Assert::notEqual('pojeb sa', 'vyprcany test');
        return;
        $presenter = new PersonPresenter();
        /*
         * Define a form
         */
        $form = $this->createForm([
            'person' => [
                'other_name' => [
                    'required' => true,
                ],
                'family_name' => [
                    'required' => true,
                ],
            ],
            'person_history' => [
                'school_id' => [
                    'required' => true,
                ],
                'study_year' => [
                    'required' => true,
                ],
                'class' => [
                    'required' => false,
                ],
            ],
            'post_contact_p' => [
                'address' => [
                    'required' => true,
                ],
            ],
            'person_info' => [
                'email' => [
                    'required' => true,
                ],
                'origin' => [
                    'required' => false,
                ],
                'agreed' => [
                    'required' => true,
                ],
            ],
        ], 2000);

        /*
         * Fill user data
         */
        $form->setValues([
            ExtendedPersonHandler::CONT_AGGR => [
                ExtendedPersonHandler::EL_PERSON => "__promise",
                ExtendedPersonHandler::CONT_PERSON => [
                    '_c_compact' => " ",
                    'person' => [
                        'other_name' => "Jana",
                        'family_name' => "Triková",
                    ],
                    'person_history' => [
                        'school_id__meta' => "JS",
                        'school_id' => "1",
                        'study_year' => "2",
                        'class' => "2.F",
                    ],
                    'post_contact_p' => [
                        'address' => [
                            'target' => "Krtkova 12",
                            'city' => "Pohádky",
                            'postal_code' => "43243",
                            'country_iso' => "",
                        ],
                    ],
                    'person_info' => [
                        'email' => "jana@sfsd.com",
                        'origin' => "dfsd",
                        'agreed' => "on",
                    ],
                ],
            ]
        ]);
        $form->validate();

        /*
         * Check
         */
        $result = $this->fixture->handleForm($form, $presenter);
        Assert::same(ExtendedPersonHandler::RESULT_OK_NEW_LOGIN, $result);

        $person = $this->fixture->getPerson();
        Assert::same('Jana', $person->other_name);
        Assert::same('Triková', $person->family_name);

        $contestants = $person->getContestants(ModelContest::ID_FYKOS);
        Assert::same(1, count($contestants));

        $info = $person->getInfo();
        Assert::same('jana@sfsd.com', $info->email);

        $address = $person->getPermanentAddress();
        Assert::same('Krtkova 12', $address->target);
        Assert::same('43243', $address->postal_code);
        Assert::notEqual(null, $address->region_id);
    }

    private function createForm($fieldsDefinition, $acYear) {
        $form = new Form();
        $container = new ContainerWithOptions();
        $form->addComponent($container, ExtendedPersonHandler::CONT_AGGR);

        $searchType = ReferencedPersonFactory::SEARCH_NONE;
        $allowClear = false;
        $modifiabilityResolver = $visibilityResolver = new TestResolver();
        $components = $this->referencedPersonFactory->createReferencedPerson($fieldsDefinition, $acYear, $searchType, $allowClear, $modifiabilityResolver, $visibilityResolver, null);

        $container->addComponent($components[0], ExtendedPersonHandler::EL_PERSON);
        $container->addComponent($components[1], ExtendedPersonHandler::CONT_PERSON);

        return $form;
    }

}

/*
 * Mock classes
 */

class PersonPresenter extends BasePresenter implements IExtendedPersonPresenter {

    public function getModel() {

    }

    public function messageCreate() {

    }

    public function messageEdit() {

    }

    public function messageError() {

    }

    public function messageExists() {

    }

    public function flashMessage($message, $type = 'info') {

    }

}

class TestResolver implements IVisibilityResolver, IModifiabilityResolver {

    public function getResolutionMode(ModelPerson $person) {
        return ReferencedPersonHandler::RESOLUTION_EXCEPTION;
    }

    public function isModifiable(ModelPerson $person) {
        return true;
    }

    public function isVisible(ModelPerson $person) {
        return true;
    }

}

$testCase = new ExtendedPersonHandlerTest($container);
$testCase->run();
