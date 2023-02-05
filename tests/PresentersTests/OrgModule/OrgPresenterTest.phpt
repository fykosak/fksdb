<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\OrgModule;

// phpcs:disable
$container = require '../../Bootstrap.php';

// phpcs:enable
use FKSDB\Components\EntityForms\OrgFormComponent;
use FKSDB\Models\ORM\Models\OrgModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\OrgService;
use Nette\Application\Responses\RedirectResponse;
use Tester\Assert;

class OrgPresenterTest extends AbstractOrgPresenterTestCase
{
    private PersonModel $person;
    private OrgModel $org;
    private PersonModel $orgPerson;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginUser();
        $this->container->getByType(OrgService::class)->storeModel(
            ['person_id' => $this->cartesianPerson->person_id, 'contest_id' => 1, 'since' => 1, 'order' => 1]
        );

        $this->orgPerson = $this->createPerson('Tester_L', 'Testrovič_L');
        $this->org = $this->container->getByType(OrgService::class)->storeModel([
            'person_id' => $this->orgPerson->person_id,
            'contest_id' => 1,
            'since' => 0,
            'order' => 0,
            'domain_alias' => 'a',
        ]);
        $this->person = $this->createPerson('Tester_C', 'Testrovič_C');
    }

    public function testList(): void
    {
        $request = $this->createGetRequest('list', []);
        $response = $this->fixture->run($request);
        $html = $this->assertPageDisplay($response);
        Assert::contains('Tester_L', $html);
        Assert::contains('Testrovič_L', $html);
        Assert::contains('Still organizes', $html);
    }

    public function testCreate(): void
    {
        $init = $this->countOrgs();
        $response = $this->createFormRequest('create', [
            OrgFormComponent::CONTAINER => [
                'person_id' => (string)$this->person->person_id,
                'person_id_container' => self::personToValues($this->person),
                'since' => "1",
                'order' => "0",
                'domain_alias' => 't',
            ],
        ]);
        Assert::type(RedirectResponse::class, $response);

        $after = $this->countOrgs();
        Assert::equal($init + 1, $after);
    }

    public function testOutRangeCreate(): void
    {
        $init = $this->countOrgs();
        $response = $this->createFormRequest('create', [
            OrgFormComponent::CONTAINER => [
                'person_id' => (string)$this->person->person_id,
                'since' => "2", // out of range
                'order' => "0",
                'domain_alias' => 't',
            ],
        ]);
        $html = $this->assertPageDisplay($response);
        Assert::contains('has-error', $html);
        $after = $this->countOrgs();
        Assert::equal($init, $after);
    }

    public function testModelErrorCreate(): void
    {
        $init = $this->countOrgs();
        $response = $this->createFormRequest('create', [
            OrgFormComponent::CONTAINER => [
                'person_id' => null, // empty personId
                'since' => "1",
                'order' => "0",
                'domain_alias' => 't',
            ],
        ]);
        $html = $this->assertPageDisplay($response);
        Assert::contains('alert-danger', $html);
        $after = $this->countOrgs();
        Assert::equal($init, $after);
    }

    public function testEdit(): void
    {
        $response = $this->createFormRequest('edit', [
            OrgFormComponent::CONTAINER => [
                'person_id' => (string)$this->orgPerson->person_id,
                'person_id_container' => self::personToValues($this->person),
                'since' => "1",
                'order' => "2",
                'domain_alias' => 'b',
            ],
        ], [
            'id' => (string)$this->org->org_id,
        ]);
        Assert::type(RedirectResponse::class, $response);
        /** @var OrgModel $org */
        $org = $this->container
            ->getByType(OrgService::class)
            ->getTable()
            ->where(['org_id' => $this->org->org_id])
            ->fetch();
        Assert::equal('b', $org->domain_alias);
        Assert::equal(2, $org->order);
    }

    public function testDetail(): void
    {
        $request = $this->createGetRequest('list', []);
        $response = $this->fixture->run($request);
        $html = $this->assertPageDisplay($response);
        Assert::contains('Tester_L', $html);
        Assert::contains('Testrovič_L', $html);
        Assert::contains('Still organizes', $html);
    }

    protected function getPresenterName(): string
    {
        return 'Org:Org';
    }

    private function countOrgs(): int
    {
        return $this->container
            ->getByType(OrgService::class)
            ->getTable()
            ->where(['person_id' => $this->person->person_id])
            ->count('*');
    }
}

// phpcs:disable
$testCase = new OrgPresenterTest($container);
$testCase->run();
// phpcs:enable
