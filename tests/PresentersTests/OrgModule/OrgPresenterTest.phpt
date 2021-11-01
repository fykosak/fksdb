<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\OrgModule;

$container = require '../../Bootstrap.php';

use FKSDB\Components\EntityForms\OrgFormComponent;
use FKSDB\Models\ORM\DbNames;
use Nette\Application\Responses\RedirectResponse;
use Tester\Assert;

/**
 * Class OrgPresenterTest
 * @author Michal Červeňák <miso@fykos.cz>
 */
class OrgPresenterTest extends AbstractOrgPresenterTestCase
{

    /** @var int */
    private $personId;
    /** @var int */
    private $orgId;
    /** @var int */
    private $orgPersonId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginUser();
        $this->insert(
            DbNames::TAB_ORG,
            ['person_id' => $this->cartesianPersonId, 'contest_id' => 1, 'since' => 1, 'order' => 1]
        );

        $this->orgPersonId = $this->createPerson('Tester_L', 'Testrovič_L');
        $this->orgId = $this->insert(
            DbNames::TAB_ORG,
            ['person_id' => $this->orgPersonId, 'contest_id' => 1, 'since' => 0, 'order' => 0, 'domain_alias' => 'a']
        );
        $this->personId = $this->createPerson('Tester_C', 'Testrovič_C');
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
                'person_id' => (string)$this->personId,
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
                'person_id' => (string)$this->personId,
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
                'person_id__meta' => (string)$this->orgPersonId,
                'since' => (string)1,
                'order' => (string)2,
                'domain_alias' => 'b',
            ],
        ], [
            'id' => (string)$this->orgId,
        ]);
        Assert::type(RedirectResponse::class, $response);
        $org = $this->explorer->query('SELECT * FROM org where org_id=?', $this->orgId)->fetch();
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

    protected function tearDown(): void
    {
        $this->truncateTables([DbNames::TAB_ORG]);
        parent::tearDown();
    }

    private function countOrgs(): int
    {
        return $this->explorer->query('SELECT * FROM org where person_id=?', $this->personId)->getRowCount();
    }
}

$testCase = new OrgPresenterTest($container);
$testCase->run();
