<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\OrgModule;

$container = require '../../Bootstrap.php';

use FKSDB\Components\EntityForms\OrgFormComponent;
use FKSDB\Models\ORM\Models\OrgModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\ServiceOrg;
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
        $this->getContainer()->getByType(ServiceOrg::class)->createNewModel(
            ['person_id' => $this->cartesianPerson->person_id, 'contest_id' => 1, 'since' => 1, 'order' => 1]
        );

        $this->orgPerson = $this->createPerson('Tester_L', 'Testrovič_L');
        $this->org = $this->getContainer()->getByType(ServiceOrg::class)->createNewModel([
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
        Assert::contains('Still organises', $html);
    }

    public function testCreate(): void
    {
        $init = $this->countOrgs();
        $response = $this->createFormRequest('create', [
            OrgFormComponent::CONTAINER => [
                'person_id__meta' => 'JS',
                'person_id' => (string)$this->person->person_id,
                'since' => (string)1,
                'order' => (string)0,
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
                'person_id__meta' => 'JS',
                'person_id' => (string)$this->person->person_id,
                'since' => (string)2, // out of range
                'order' => (string)0,
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
                'person_id__meta' => 'JS',
                'person_id' => null, // empty personId
                'since' => (string)1,
                'order' => (string)0,
                'domain_alias' => 't',
            ],
        ]);
        $html = $this->assertPageDisplay($response);
        Assert::contains('SQLSTATE[23000]:', $html);
        $after = $this->countOrgs();
        Assert::equal($init, $after);
    }

    public function testEdit(): void
    {
        $response = $this->createFormRequest('edit', [
            OrgFormComponent::CONTAINER => [
                'person_id__meta' => (string)$this->orgPerson->person_id,
                'since' => (string)1,
                'order' => (string)2,
                'domain_alias' => 'b',
            ],
        ], [
            'id' => (string)$this->org->org_id,
        ]);
        Assert::type(RedirectResponse::class, $response);
        $org = $this->getContainer()
            ->getByType(ServiceOrg::class)
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
        Assert::contains('Still organises', $html);
    }

    protected function getPresenterName(): string
    {
        return 'Org:Org';
    }

    private function countOrgs(): int
    {
        return $this->getContainer()
            ->getByType(ServiceOrg::class)
            ->getTable()
            ->where(['person_id' => $this->person->person_id])
            ->count('*');
    }
}

$testCase = new OrgPresenterTest($container);
$testCase->run();
