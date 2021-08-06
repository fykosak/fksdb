<?php

declare(strict_types=1);

namespace FKSDB\Tests\ModelsTests\Persons;

$container = require '../../Bootstrap.php';

use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelContestYear;
use FKSDB\Models\ORM\Services\ServiceContest;
use FKSDB\Models\ORM\Services\ServiceContestant;
use FKSDB\Tests\MockEnvironment\MockApplicationTrait;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\DI\Container;
use Nette\Forms\Form;
use FKSDB\Models\Persons\ExtendedPersonHandler;
use FKSDB\Models\Persons\ExtendedPersonHandlerFactory;
use Tester\Assert;

class ExtendedPersonHandlerTest extends DatabaseTestCase
{
    use MockApplicationTrait;

    private ExtendedPersonHandler $fixture;
    private ReferencedPersonFactory $referencedPersonFactory;
    private ModelContestYear $contestYear;

    /**
     * ExtendedPersonHandlerTest constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->setContainer($container);
        $this->referencedPersonFactory = $this->container->getByType(ReferencedPersonFactory::class);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
        $handlerFactory = $this->getContainer()->getByType(ExtendedPersonHandlerFactory::class);

        $service = $this->getContainer()->getByType(ServiceContestant::class);
        $this->contestYear = $this->container->getByType(ServiceContest::class)
            ->findByPrimary(ModelContest::ID_FYKOS)
            ->getContestYear(1);
        $this->fixture = $handlerFactory->create($service, $this->contestYear, 'cs');
    }

    protected function tearDown(): void
    {
        $this->truncateTables([DbNames::TAB_CONTESTANT_BASE, DbNames::TAB_AUTH_TOKEN, DbNames::TAB_LOGIN]);

        parent::tearDown();
    }

    public function testNewPerson(): void
    {
        $presenter = new PersonPresenter();
        // Define a form

        $form = $this->createForm(
            [
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
            ]
        );

        // Fill user data
        $form->setValues(
            [
                ExtendedPersonHandler::CONT_AGGR => [
                    ExtendedPersonHandler::EL_PERSON => "__promise",
                    ExtendedPersonHandler::EL_PERSON . '_1' => [
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
                                'country_iso' => null,
                            ],
                        ],
                        'person_info' => [
                            'email' => "jana@sfsd.com",
                            'origin' => "dfsd",
                            'agreed' => "on",
                        ],
                    ],
                ],
            ]
        );
        $form->validate();

        // Check
        $result = $this->fixture->handleForm($form, $presenter, true);
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

    private function createForm(array $fieldsDefinition): Form
    {
        $form = new Form();
        $container = new ContainerWithOptions();
        $form->addComponent($container, ExtendedPersonHandler::CONT_AGGR);

        $referencedId = $this->referencedPersonFactory->createReferencedPerson(
            $fieldsDefinition,
            $this->contestYear,
            PersonSearchContainer::SEARCH_NONE,
            false,
            new TestResolver(),
            new TestResolver()
        );

        $container->addComponent($referencedId, ExtendedPersonHandler::EL_PERSON);
        // $container->addComponent($component->getReferencedContainer(), ExtendedPersonHandler::CONT_PERSON);

        return $form;
    }
}

/*
 * Mock classes
 */

$testCase = new ExtendedPersonHandlerTest($container);
$testCase->run();
