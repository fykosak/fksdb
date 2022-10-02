<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\OrgModule;

// phpcs:disable
$container = require '../../Bootstrap.php';

// phpcs:enable
use FKSDB\Components\EntityForms\SchoolFormComponent;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\AddressService;
use FKSDB\Models\ORM\Services\OrgService;
use FKSDB\Models\ORM\Services\SchoolService;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Tester\Assert;

class SchoolPresenterTest extends AbstractOrgPresenterTestCase
{
    private SchoolModel $school;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginUser();
        $this->getContainer()->getByType(OrgService::class)->storeModel(
            ['person_id' => $this->cartesianPerson->person_id, 'contest_id' => 1, 'since' => 1, 'order' => 1]
        );
        $address = $this->getContainer()->getByType(AddressService::class)->storeModel([
            'first_row' => 'PU',
            'second_row' => 'PU',
            'target' => 'PU',
            'city' => 'PU',
            'postal_code' => '02001',
            'region_id' => '1',
        ]);
        $this->school = $this->getContainer()->getByType(SchoolService::class)->storeModel([
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
            SchoolFormComponent::CONT_ADDRESS => [
                'first_row' => 'PU',
                'second_row' => 'PU',
                'target' => 'PU',
                'city' => 'PU',
                'postal_code' => '02001',
                'region_id' => '1',
            ],
            SchoolFormComponent::CONT_SCHOOL => [
                'name' => 'Test school',
                'name_abbrev' => 'T school',
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
                SchoolFormComponent::CONT_ADDRESS => [
                    'first_row' => 'PU',
                    'second_row' => 'PU',
                    'target' => 'PU',
                    'city' => 'PU edited',
                    'postal_code' => '02001',
                    'region_id' => '1',
                ],
                SchoolFormComponent::CONT_SCHOOL => [
                    'name' => 'Test school edited',
                    'name_abbrev' => 'T school',
                ],
            ],
            [
                'id' => $this->school->school_id,
            ]
        );
        if ($response instanceof TextResponse) {
            file_put_contents('t.html', (string)$response->getSource());
        }
        Assert::type(RedirectResponse::class, $response);
        $after = $this->countSchools();
        Assert::equal($init, $after);

        $school = $this->getContainer()
            ->getByType(SchoolService::class)
            ->findByPrimary($this->school->school_id);

        Assert::equal('Test school edited', $school->name);
        $address = $this->getContainer()
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
        return $this->getContainer()->getByType(SchoolService::class)->getTable()->count('*');
    }
}

// phpcs:disable
$testCase = new SchoolPresenterTest($container);
$testCase->run();
// phpcs:enable
