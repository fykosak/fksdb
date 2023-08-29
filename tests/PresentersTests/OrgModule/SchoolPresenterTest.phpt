<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\OrgModule;

// phpcs:disable
$container = require '../../Bootstrap.php';

// phpcs:enable
use FKSDB\Components\EntityForms\SchoolFormComponent;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Models\ORM\Models\AddressModel;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\AddressService;
use FKSDB\Models\ORM\Services\CountryService;
use FKSDB\Models\ORM\Services\OrgService;
use FKSDB\Models\ORM\Services\SchoolService;
use Nette\Application\Responses\RedirectResponse;
use Tester\Assert;

class SchoolPresenterTest extends AbstractOrgPresenterTestCase
{
    private SchoolModel $school;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginUser();
        $this->container->getByType(OrgService::class)->storeModel(
            ['person_id' => $this->cartesianPerson->person_id, 'contest_id' => 1, 'since' => 1, 'order' => 1]
        );
        /** @var AddressModel $address */
        $address = $this->container->getByType(AddressService::class)->storeModel([
            'first_row' => 'PU',
            'second_row' => 'PU',
            'target' => 'PU',
            'city' => 'PU',
            'postal_code' => '02001',
            'country_id' => (string)CountryService::SLOVAKIA,
        ]);
        $this->school = $this->container->getByType(SchoolService::class)->storeModel([
            'address_id' => $address->address_id,
            'name' => 'Test school',
            'name_abbrev' => 'T school',
        ]);
    }

    public function testList(): void
    {
        $request = $this->createGetRequest('list', []);
        $response = $this->fixture->run($request);
        $html = $this->assertPageDisplay($response);
        Assert::contains('Test school', $html);
        Assert::contains('PU', $html);
    }

    public function testCreate(): void
    {
        $init = $this->countSchools();
        $response = $this->createFormRequest('create', [
            SchoolFormComponent::CONT_SCHOOL => [
                'name' => 'Test school',
                'name_abbrev' => 'T school',
                'address_id' => ReferencedId::VALUE_PROMISE,
                'address_id_container' => [
                    'first_row' => 'PU',
                    'second_row' => 'PU',
                    'target' => 'PU',
                    'city' => 'PU',
                    'postal_code' => '02001',
                    'country_id' => (string)CountryService::SLOVAKIA,
                ],
            ],
        ]);
        Assert::type(RedirectResponse::class, $response);
        $after = $this->countSchools();
        Assert::equal($init + 1, $after);
    }

    public function testEdit(): void
    {
        $init = $this->countSchools();
        $response = $this->createFormRequest(
            'edit',
            [
                SchoolFormComponent::CONT_SCHOOL => [
                    'name' => 'Test school edited',
                    'name_abbrev' => 'T school',
                    'address_id' => (string)$this->school->address_id,
                    'address_id_container' => [
                        'first_row' => 'PU',
                        'second_row' => 'PU',
                        'target' => 'PU',
                        'city' => 'PU edited',
                        'postal_code' => '02001',
                        'country_id' => (string)CountryService::SLOVAKIA,
                    ],
                ],
            ],
            [
                'id' => $this->school->school_id,
            ]
        );
        Assert::type(RedirectResponse::class, $response);
        $after = $this->countSchools();
        Assert::equal($init, $after);

        $school = $this->container
            ->getByType(SchoolService::class)
            ->findByPrimary($this->school->school_id);

        Assert::equal('Test school edited', $school->name);
        $address = $this->container
            ->getByType(AddressService::class)
            ->findByPrimary($school->address_id);

        Assert::equal('PU edited', $address->city);
    }

    protected function getPresenterName(): string
    {
        return 'Org:School';
    }

    private function countSchools(): int
    {
        return $this->container->getByType(SchoolService::class)->getTable()->count('*');
    }
}

// phpcs:disable
$testCase = new SchoolPresenterTest($container);
$testCase->run();
// phpcs:enable
