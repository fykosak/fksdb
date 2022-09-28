<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\EventModule;

$container = require '../../Bootstrap.php';

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\ContestYearService;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamMemberService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\ORM\Services\SchoolService;
use FKSDB\Tests\PresentersTests\EntityPresenterTestCase;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Tester\Assert;

class TeamApplicationPresenterTest extends EntityPresenterTestCase
{
    private PersonModel $personA;
    private PersonModel $personB;
    private PersonModel $personC;
    private PersonModel $personD;
    private PersonModel $personE;

    private EventModel $event;

    protected function setUp(): void
    {
        parent::setUp();
        $school = $this->getContainer()->getByType(SchoolService::class)->getTable()->fetch();
        $this->mockApplication();

        $this->personA = $this->createPerson('A', 'A', ['email' => 'a@a.a'], ['login' => 'AAAAAA', 'hash' => 'AAAAAA']);
        $this->createPersonHistory($this->personA, ContestYearService::getCurrentAcademicYear(), $school, 1, '1A');

        $this->personB = $this->createPerson('B', 'B', ['email' => 'b@b.b'], ['login' => 'BBBBBB', 'hash' => 'BBBBBB']);
        $this->createPersonHistory($this->personB, ContestYearService::getCurrentAcademicYear(), $school, 2, '2A');

        $this->personC = $this->createPerson('C', 'C', ['email' => 'c@c.c'], ['login' => 'CCCCCC', 'hash' => 'CCCCCC']);
        $this->createPersonHistory($this->personC, ContestYearService::getCurrentAcademicYear(), $school, 3, '3C');

        $this->personD = $this->createPerson('D', 'D', ['email' => 'd@d.d'], ['login' => 'DDDDDD', 'hash' => 'DDDDDD']);
        $this->createPersonHistory($this->personD, ContestYearService::getCurrentAcademicYear(), $school, 4, '4D');

        $this->personE = $this->createPerson('E', 'E', ['email' => 'e@e.e'], ['login' => 'EEEEEE', 'hash' => 'EEEEEE']);
        $this->createPersonHistory($this->personE, ContestYearService::getCurrentAcademicYear(), $school, 9, '9D');

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
        $this->logOut($this->fixture);
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

    public function testCreateSelf(): void
    {
        $this->authenticateLogin($this->personA->getLogin(), $this->fixture);
        $data = [
            'team' => [
                'name' => 'test team A',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_0_container' => self::personToValues($this->personA),
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
        ];
        $response = $this->createFormRequest('create', $data);
        Assert::type(RedirectResponse::class, $response);
    }

    public function testCreateOrg(): void
    {
        $this->loginUser(5);
        $data = [
            'team' => [
                'name' => 'test team A',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_0_container' => self::personToValues($this->personA),
            'member_1' => (string)$this->personB->person_id,
            'member_1_container' => self::personToValues($this->personB),
            'member_2' => (string)$this->personC->person_id,
            'member_2_container' => self::personToValues($this->personC),
            'member_3' => (string)$this->personD->person_id,
            'member_3_container' => self::personToValues($this->personD),
            'member_4' => (string)$this->personE->person_id,
            'member_4_container' => self::personToValues($this->personE),
        ];
        $response = $this->createFormRequest('create', $data);
        Assert::type(RedirectResponse::class, $response);
        /** @var TeamModel2 $team */
        $team = $this->getContainer()->getByType(TeamService2::class)->getTable()
            ->where('event_id', $this->event->event_id)
            ->where('name', 'test team A')
            ->fetch();
        Assert::type(TeamModel2::class, $team);
        Assert::same('B', $team->category->value);
        Assert::same(5, $team->getMembers()->count('*'));
    }

    public function testCreateAnonymousOutDate(): void
    {
        $this->logOut($this->fixture);
        $this->outDateEvent();
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
        Assert::exception(fn() => $this->createFormRequest('create', $data), ForbiddenRequestException::class);
    }

    public function testCreateLoggedInOutDate(): void
    {
        $this->outDateEvent();
        $this->authenticatePerson($this->personA, $this->fixture);
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
        Assert::exception(fn() => $this->createFormRequest('create', $data), ForbiddenRequestException::class);
    }

    public function testCreateOrgOutDate(): void
    {
        $this->outDateEvent();
        $this->loginUser(5);
        $data = [
            'team' => [
                'name' => 'test team B',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_0_container' => self::personToValues($this->personA),
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
        ];
        $response = $this->createFormRequest('create', $data);
        Assert::type(RedirectResponse::class, $response);
    }

    public function testEditAnonymous(): void
    {
        $this->logOut($this->fixture);
        $team = $this->createTeam('Original', [$this->personA]);
        $data = [
            'team' => [
                'name' => 'Edited',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
        ];
        /** @var RedirectResponse $response */
        $response = $this->createFormRequest('edit', $data, ['id' => $team->getPrimary()]);
        Assert::type(RedirectResponse::class, $response);
        Assert::contains('/auth/login', $response->getUrl());
        Assert::same(
            'Original',
            $this->getContainer()->getByType(TeamService2::class)->findByPrimary($team->getPrimary())->name
        );
    }

    public function testEditLoggedInOwnTeam(): void
    {
        $this->authenticateLogin($this->personA->getLogin(), $this->fixture);
        $team = $this->createTeam('Original', [$this->personA]);
        $data = [
            'team' => [
                'name' => 'Edited',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_0_container' => self::personToValues($this->personA),
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
        ];
        /** @var RedirectResponse $response */
        $response = $this->createFormRequest('edit', $data, ['id' => $team->getPrimary()]);
        Assert::type(RedirectResponse::class, $response);
        Assert::contains('detail', $response->getUrl());
        Assert::same(
            'Edited',
            $this->getContainer()->getByType(TeamService2::class)->findByPrimary($team->getPrimary())->name
        );
    }

    public function testEditLoggedInForeignTeam(): void
    {
        $this->authenticateLogin($this->personB->getLogin(), $this->fixture);
        $team = $this->createTeam('Original', [$this->personA]);
        $data = [
            'team' => [
                'name' => 'Edited',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
        ];
        Assert::exception(
            fn() => $this->createFormRequest('edit', $data, ['id' => $team->getPrimary()]),
            ForbiddenRequestException::class
        );
        Assert::same(
            'Original',
            $this->getContainer()->getByType(TeamService2::class)->findByPrimary($team->getPrimary())->name
        );
    }

    public function testEditOrg(): void
    {
        $this->loginUser(5);
        $team = $this->createTeam('Original', [$this->personA]);
        $data = [
            'team' => [
                'name' => 'Edited',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_0_container' => self::personToValues($this->personA),
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
        ];
        /** @var RedirectResponse $response */
        $response = $this->createFormRequest('edit', $data, ['id' => $team->getPrimary()]);
        Assert::type(RedirectResponse::class, $response);
        Assert::contains('detail', $response->getUrl());
        Assert::same(
            'Edited',
            $this->getContainer()->getByType(TeamService2::class)->findByPrimary($team->getPrimary())->name
        );
    }

    public function testEditLoggedInOutDate(): void
    {
        $this->outDateEvent();
        $this->authenticateLogin($this->personA->getLogin(), $this->fixture);
        $team = $this->createTeam('Original', [$this->personA]);
        $data = [
            'team' => [
                'name' => 'Edited',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_0_container' => self::personToValues($this->personA),
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
        ];
        Assert::exception(
            fn() => $this->createFormRequest('edit', $data, ['id' => $team->getPrimary()]),
            ForbiddenRequestException::class
        );
        Assert::same(
            'Original',
            $this->getContainer()->getByType(TeamService2::class)->findByPrimary($team->getPrimary())->name
        );
    }

    public function testEditOrgOutDate(): void
    {
        $this->outDateEvent();
        $this->loginUser(5);
        $team = $this->createTeam('Original', [$this->personA]);
        $data = [
            'team' => [
                'name' => 'Edited',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_0_container' => self::personToValues($this->personA),
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
        ];
        /** @var RedirectResponse $response */
        $response = $this->createFormRequest('edit', $data, ['id' => $team->getPrimary()]);
        Assert::type(RedirectResponse::class, $response);
        Assert::contains('detail', $response->getUrl());
        Assert::same(
            'Edited',
            $this->getContainer()->getByType(TeamService2::class)->findByPrimary($team->getPrimary())->name
        );
    }

    public function testEditReplaceMember(): void
    {
        $this->loginUser(5);
        $team = $this->createTeam('Original', [$this->personA]);
        $data = [
            'team' => [
                'name' => 'Edited',
            ],
            'member_0' => (string)$this->personB->person_id,
            'member_0_container' => self::personToValues($this->personB),
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
        ];
        $response = $this->createFormRequest('edit', $data, ['id' => $team->getPrimary()]);
        Assert::type(RedirectResponse::class, $response);
        $newTeam = $this->getContainer()->getByType(TeamService2::class)->findByPrimary($team->getPrimary());
        Assert::same(1, $newTeam->getMembers()->count('*'));
        Assert::same($this->personB->person_id, $newTeam->getMembers()->fetch()->person_id);
    }

    public function testCreateDuplicateMember(): void
    {
        $before = $this->getContainer()->getByType(TeamService2::class)->getTable()->count('*');
        $data = [
            'team' => [
                'name' => 'Edited',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_1' => (string)$this->personA->person_id,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
        ];
        $response = $this->createFormRequest('create', $data);
        Assert::type(TextResponse::class, $response);
        $after = $this->getContainer()->getByType(TeamService2::class)->getTable()->count('*');
        Assert::same($before, $after);
    }

    public function testCreateDuplicateMember2(): void
    {
        $this->createTeam('Unique', [$this->personA]);
        $before = $this->getContainer()->getByType(TeamService2::class)->getTable()->count('*');

        $data = [
            'team' => [
                'name' => 'Edited',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
        ];
        $response = $this->createFormRequest('create', $data);
        Assert::type(TextResponse::class, $response);
        $after = $this->getContainer()->getByType(TeamService2::class)->getTable()->count('*');
        Assert::same($before, $after);
    }

    public function testEditDuplicateTeamName(): void
    {
        $this->createTeam('Unique', [$this->personB]);
        $before = $this->getContainer()->getByType(TeamService2::class)->getTable()->count('*');

        $data = [
            'team' => [
                'name' => 'Unique',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
        ];
        $response = $this->createFormRequest('create', $data);
        Assert::type(TextResponse::class, $response);
        $after = $this->getContainer()->getByType(TeamService2::class)->getTable()->count('*');
        Assert::same($before, $after);
    }

    protected function createTeam(string $name, array $persons): TeamModel2
    {
        $team = $this->getContainer()->getByType(TeamService2::class)->storeModel([
            'name' => $name,
            'category' => 'B',
            'event_id' => $this->event->event_id,
        ]);
        /** @var PersonModel $person */
        foreach ($persons as $person) {
            $this->getContainer()->getByType(TeamMemberService::class)->storeModel([
                'person_id' => $person->person_id,
                'fyziklani_team_id' => $team->fyziklani_team_id,
            ]);
        }
        return $team;
    }

    public function outDateEvent(): void
    {
        $this->getContainer()->getByType(EventService::class)->storeModel([
            'registration_begin' => (new \DateTime())->sub(new \DateInterval('P2D')),
            'registration_end' => (new \DateTime())->sub(new \DateInterval('P1D')),
        ], $this->event);
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
