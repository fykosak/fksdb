<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\OrganizerModule;

// phpcs:disable
$container = require '../../Bootstrap.php';

// phpcs:enable
use FKSDB\Components\EntityForms\OrganizerFormComponent;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\OrganizerModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\ContestService;
use FKSDB\Models\ORM\Services\OrganizerService;
use Nette\Application\Responses\RedirectResponse;
use Tester\Assert;

class OrganizerPresenterTest extends AbstractOrganizerPresenterTestCase
{
    private PersonModel $person;
    private OrganizerModel $organizer;
    private PersonModel $organizerPerson;
    private ContestModel $contest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginUser();
        $this->contest = $this->container->getByType(ContestService::class)->findByPrimary(ContestModel::ID_FYKOS);
        $this->container->getByType(OrganizerService::class)->storeModel(
            ['person_id' => $this->cartesianPerson->person_id, 'contest_id' => 1, 'since' => 1, 'order' => 1]
        );

        $this->organizerPerson = $this->createPerson('Tester_L', 'Testrovič_L');
        $this->organizer = $this->container->getByType(OrganizerService::class)->storeModel([
            'person_id' => $this->organizerPerson->person_id,
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
        $init = $this->countOrganizers();
        $response = $this->createFormRequest('create', [
            OrganizerFormComponent::CONTAINER => [
                'person_id' => (string)$this->person->person_id,
                'person_id_container' => self::personToValues($this->contest, $this->person),
                'since' => "1",
                'order' => "0",
                'domain_alias' => 't',
            ],
        ]);
        Assert::type(RedirectResponse::class, $response);

        $after = $this->countOrganizers();
        Assert::equal($init + 1, $after);
    }

    public function testOutRangeCreate(): void
    {
        $init = $this->countOrganizers();
        $response = $this->createFormRequest('create', [
            OrganizerFormComponent::CONTAINER => [
                'person_id' => (string)$this->person->person_id,
                'since' => "2", // out of range
                'order' => "0",
                'domain_alias' => 't',
            ],
        ]);
        $html = $this->assertPageDisplay($response);
        Assert::contains('has-error', $html);
        $after = $this->countOrganizers();
        Assert::equal($init, $after);
    }

    public function testModelErrorCreate(): void
    {
        $init = $this->countOrganizers();
        $response = $this->createFormRequest('create', [
            OrganizerFormComponent::CONTAINER => [
                'person_id' => null, // empty personId
                'since' => "1",
                'order' => "0",
                'domain_alias' => 't',
            ],
        ]);
        $html = $this->assertPageDisplay($response);
        Assert::contains('alert-danger', $html);
        $after = $this->countOrganizers();
        Assert::equal($init, $after);
    }

    public function testEdit(): void
    {
        $response = $this->createFormRequest('edit', [
            OrganizerFormComponent::CONTAINER => [
                'person_id' => (string)$this->organizerPerson->person_id,
                'person_id_container' => self::personToValues($this->contest, $this->person),
                'since' => "1",
                'order' => "2",
                'domain_alias' => 'b',
            ],
        ], [
            'id' => (string)$this->organizer->org_id,
        ]);
        Assert::type(RedirectResponse::class, $response);
        /** @var OrganizerModel $organizer */
        $organizer = $this->container
            ->getByType(OrganizerService::class)
            ->getTable()
            ->where(['org_id' => $this->organizer->org_id])
            ->fetch();
        Assert::equal('b', $organizer->domain_alias);
        Assert::equal(2, $organizer->order);
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
        return 'Organizer:Organizer';
    }

    private function countOrganizers(): int
    {
        return $this->container
            ->getByType(OrganizerService::class)
            ->getTable()
            ->where(['person_id' => $this->person->person_id])
            ->count('*');
    }
}

// phpcs:disable
$testCase = new OrganizerPresenterTest($container);
$testCase->run();
// phpcs:enable
