<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\OrgModule;

$container = require '../../Bootstrap.php';

use FKSDB\Components\EntityForms\OrgFormComponent;
use FKSDB\Models\ORM\Models\OrgModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\OrgService;
use FKSDB\Models\YearCalculator;
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
        $this->getContainer()->getByType(OrgService::class)->storeModel(
            ['person_id' => $this->cartesianPerson->person_id, 'contest_id' => 1, 'since' => 1, 'order' => 1]
        );

        $this->orgPerson = $this->createPerson('Tester_L', 'Testrovič_L');
        $this->org = $this->getContainer()->getByType(OrgService::class)->storeModel([
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
                'person_id' => (string)$this->person->person_id,
                'person_id_1' => self::personToValues($this->person),
                'since' => (string)1,
                'order' => (string)0,
                'domain_alias' => 't',
            ],
        ]);
       // file_put_contents('r.html', (string)$response->getSource());
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
                'person_id' => null, // empty personId
                'since' => (string)1,
                'order' => (string)0,
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
                'person_id_1' => self::personToValues($this->person),
                'since' => (string)1,
                'order' => (string)2,
                'domain_alias' => 'b',
            ],
        ], [
            'id' => (string)$this->org->org_id,
        ]);
        Assert::type(RedirectResponse::class, $response);
        /** @var OrgModel $org */
        $org = $this->getContainer()
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
        Assert::contains('Still organises', $html);
    }

    protected function getPresenterName(): string
    {
        return 'Org:Org';
    }

    private function countOrgs(): int
    {
        return $this->getContainer()
            ->getByType(OrgService::class)
            ->getTable()
            ->where(['person_id' => $this->person->person_id])
            ->count('*');
    }

    public static function personToValues(PersonModel $person): array
    {
        return [
            '_c_compact' => $person->getFullName(),
            'person' => [
                'other_name' => $person->other_name,
                'family_name' => $person->family_name,
            ],
            'person_info' => [
                'email' => $person->getInfo()->email,
                'born' => $person->getInfo()->born,
            ],
            'person_history' => [
                'school_id__meta' => (string)$person->getHistory(YearCalculator::getCurrentAcademicYear())->school_id,
                'school_id' => (string)$person->getHistory(YearCalculator::getCurrentAcademicYear())->school_id,
                'study_year' => (string)$person->getHistory(YearCalculator::getCurrentAcademicYear())->study_year,
            ],
            'person_has_flag' => [
                'spam_mff' => '1',
            ],
        ];
    }
}

$testCase = new OrgPresenterTest($container);
$testCase->run();
