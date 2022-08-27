<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\EventModule;

$container = require '../../Bootstrap.php';

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\ORM\Services\SchoolService;
use FKSDB\Models\YearCalculator;
use FKSDB\Tests\PresentersTests\EntityPresenterTestCase;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Tester\Assert;

class TeamApplicationPresenterTest extends EntityPresenterTestCase
{
    private PersonModel $personA;
    private PersonModel $personB;
    private PersonModel $personC;
    private EventModel $event;

    protected function setUp(): void
    {
        parent::setUp();
        $school = $this->getContainer()->getByType(SchoolService::class)->getTable()->fetch();
        $this->mockApplication();

        $this->personA = $this->createPerson('A', 'A', ['email' => 'a@a.a'], ['login' => 'AAAAAA', 'hash' => 'AAAAAA']);
        $this->createPersonHistory($this->personA, YearCalculator::getCurrentAcademicYear(), $school, 1, '1A');

        $this->personB = $this->createPerson('B', 'B', ['email' => 'b@b.b'], ['login' => 'BBBBBB', 'hash' => 'BBBBBB']);
        $this->createPersonHistory($this->personB, YearCalculator::getCurrentAcademicYear(), $school, 3, '3A');

        $this->personC = $this->createPerson('C', 'C', ['email' => 'c@c.c'], ['login' => 'CCCCCC', 'hash' => 'CCCCCC']);
        $this->createPersonHistory($this->personC, YearCalculator::getCurrentAcademicYear(), $school, 4, '4C');
        $this->event = $this->getContainer()->getByType(EventService::class)->storeModel([
            'event_type_id' => 9,
            'year' => 1,
            'event_year' => 1,
            'begin' => new \DateTime(),
            'end' => new \DateTime(),
            'registration_begin' => (new \DateTime())->sub(new \DateInterval('P1D')),
            'registration_end' => (new \DateTime())->add(new \DateInterval('P1D')),
            'name' => 'Test FOL opened',
        ]);
    }

    public function testCreateAnonymous(): void
    {
        $data = [
            'team' => [
                'name' => 'test team A',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
        ];
        /** @var TextResponse $response */
        $response = $this->createFormRequest('create', $data);
        Assert::type(RedirectResponse::class, $response);
        /** @var TeamModel2 $team */
        $team = $this->getContainer()->getByType(TeamService2::class)->getTable()
            ->where('event_id', $this->event->event_id)
            ->where('name', 'test team A')
            ->fetch();
        Assert::type(TeamModel2::class, $team);
        Assert::same('C', $team->category->value);
        Assert::same(1, $team->getMembers()->count('*'));
        Assert::same($this->personA->person_id, $team->getMembers()->fetch()->person_id);
    }

    public function testCreateOrg(): void
    {
        $data = [
            'team' => [
                'name' => 'test team A',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_1' => (string)$this->personB->person_id,
            'member_2' => (string)$this->personC->person_id,
            'member_3' => null,
            'member_4' => null,
        ];
        /** @var TextResponse $response */
        $response = $this->createFormRequest('create', $data);
        Assert::type(RedirectResponse::class, $response);
        /** @var TeamModel2 $team */
        $team = $this->getContainer()->getByType(TeamService2::class)->getTable()
            ->where('event_id', $this->event->event_id)
            ->where('name', 'test team A')
            ->fetch();
        Assert::type(TeamModel2::class, $team);
        Assert::same('B', $team->category->value);
        Assert::same(3, $team->getMembers()->count('*'));
    }

    public function testCreateAnonymousOutDate(): void
    {
        // TODO FAIL
    }

    public function testCreateLoggedInOutDate(): void
    {
        // TODO FAIL
    }

    public function testCreateOrgOutDate(): void
    {
        // TODO OK
    }

    public function testEditAnonymous(): void
    {
        // TODO FAIL
    }

    public function testEditLoggedInOwnTeam(): void
    {
        // TODO OK
    }

    public function testEditLoggedInForeignTeam(): void
    {
        // TODO FAIL
    }

    public function testEditOrg(): void
    {
        // TODO OK
    }

    public function testEditAnonymousOutDate(): void
    {
        // TODO FAIL
    }

    public function testEditLoggedInOutDate(): void
    {
        // TODO FAIL
    }

    public function testEditOrgOutDate(): void
    {
        // TODO OK
    }

    public function testEditReplaceMember(): void
    {
        // TODO OK
    }

    public function testEditDuplicateMember(): void
    {
        // TODO OK
    }

    public function testEditDuplicateTeamName(): void
    {
        // TODO OK
    }

    public function testEditCategoryProcessing(): void
    {
        // TODO OK
    }

    protected function createPostRequest(string $action, array $params, array $postData = []): Request
    {
        $params['eventId'] = $this->event->event_id;
        return parent::createPostRequest($action, $params, $postData);
    }

    protected function createGetRequest(string $action, array $params, array $postData = []): Request
    {
        $params['eventId'] = $this->event->event_id;
        return parent::createGetRequest($action, $params, $postData);
    }

    protected function getPresenterName(): string
    {
        return 'Event:TeamApplication';
    }
}

$testCase = new TeamApplicationPresenterTest($container);
$testCase->run();
