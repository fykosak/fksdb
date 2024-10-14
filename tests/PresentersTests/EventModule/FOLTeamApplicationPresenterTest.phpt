<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\EventModule;

// phpcs:disable
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamMemberService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Tester\Assert;

$container = require '../../Bootstrap.php';

// phpcs:enable

class FOLTeamApplicationPresenterTest extends TeamApplicationPresenterTestCase
{

    protected function createEvent(): EventModel
    {
        return $this->container->getByType(EventService::class)->storeModel([
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
                'game_lang' => 'en',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_0_container' => self::personToValues($this->event->event_type->contest, $this->personA),
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
            'captcha' => 'pqrt',
            'privacy' => '1',
        ];
        $response = $this->createFormRequest('create', $data);
        if ($response instanceof TextResponse) {
            file_put_contents('r.html', (string)$response->getSource());
        }

        Assert::type(RedirectResponse::class, $response);
        /** @var TeamModel2 $team */
        $team = $this->container->getByType(TeamService2::class)->getTable()
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
                'game_lang' => 'en',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_0_container' => self::personToValues($this->event->event_type->contest, $this->personA),
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
            'captcha' => 'pqrt',
            'privacy' => '1',
        ];
        $response = $this->createFormRequest('create', $data);
        if ($response instanceof TextResponse) {
            file_put_contents('r2.html', (string)$response->getSource());
        }
        Assert::type(RedirectResponse::class, $response);
    }

    public function testCreateOrganizer(): void
    {
        $this->loginUser(ContestGrantModel::EventManager);
        $data = [
            'team' => [
                'name' => 'test team A',
                'game_lang' => 'en',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_0_container' => self::personToValues($this->event->event_type->contest, $this->personA),
            'member_1' => (string)$this->personB->person_id,
            'member_1_container' => self::personToValues($this->event->event_type->contest, $this->personB),
            'member_2' => (string)$this->personC->person_id,
            'member_2_container' => self::personToValues($this->event->event_type->contest, $this->personC),
            'member_3' => (string)$this->personD->person_id,
            'member_3_container' => self::personToValues($this->event->event_type->contest, $this->personD),
            'member_4' => (string)$this->personE->person_id,
            'member_4_container' => self::personToValues($this->event->event_type->contest, $this->personE),
            'captcha' => 'pqrt',
            'privacy' => '1',
        ];
        $response = $this->createFormRequest('create', $data);
        Assert::type(RedirectResponse::class, $response);
        /** @var TeamModel2 $team */
        $team = $this->container->getByType(TeamService2::class)->getTable()
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
                'game_lang' => 'en',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_1' => (string)$this->personB->person_id,
            'member_2' => (string)$this->personC->person_id,
            'member_3' => null,
            'member_4' => null,
            'captcha' => 'pqrt',
            'privacy' => '1',
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
                'game_lang' => 'en',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
            'captcha' => 'pqrt',
            'privacy' => '1',
        ];
        Assert::exception(fn() => $this->createFormRequest('create', $data), ForbiddenRequestException::class);
    }

    public function testCreateOrganizerOutDate(): void
    {
        $this->outDateEvent();
        $this->loginUser(ContestGrantModel::EventManager);
        $data = [
            'team' => [
                'name' => 'test team B',
                'game_lang' => 'en',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_0_container' => self::personToValues($this->event->event_type->contest, $this->personA),
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
            'captcha' => 'pqrt',
            'privacy' => '1',
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
                'game_lang' => 'en',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
            'captcha' => 'pqrt',
            'privacy' => '1',
        ];
        /** @var RedirectResponse $response */
        $response = $this->createFormRequest('edit', $data, ['id' => $team->getPrimary()]);
        Assert::type(RedirectResponse::class, $response);
        Assert::contains('/auth/login', $response->getUrl());
        Assert::same(
            'Original',
            $this->container->getByType(TeamService2::class)->findByPrimary($team->getPrimary())->name
        );
    }

    public function testEditLoggedInOwnTeam(): void
    {
        $this->authenticateLogin($this->personA->getLogin(), $this->fixture);
        $team = $this->createTeam('Original', [$this->personA]);
        $data = [
            'team' => [
                'name' => 'Edited',
                'game_lang' => 'en',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_0_container' => self::personToValues($this->event->event_type->contest, $this->personA),
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
            'captcha' => 'pqrt',
            'privacy' => '1',
        ];
        /** @var RedirectResponse $response */
        $response = $this->createFormRequest('edit', $data, ['id' => $team->getPrimary()]);
        Assert::type(RedirectResponse::class, $response);
        Assert::contains('detail', $response->getUrl());
        Assert::same(
            'Edited',
            $this->container->getByType(TeamService2::class)->findByPrimary($team->getPrimary())->name
        );
    }

    public function testEditLoggedInForeignTeam(): void
    {
        $this->authenticateLogin($this->personB->getLogin(), $this->fixture);
        $team = $this->createTeam('Original', [$this->personA]);
        $data = [
            'team' => [
                'name' => 'Edited',
                'game_lang' => 'en',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
            'captcha' => 'pqrt',
            'privacy' => '1',
        ];
        Assert::exception(
            fn() => $this->createFormRequest('edit', $data, ['id' => $team->getPrimary()]),
            ForbiddenRequestException::class
        );
        Assert::same(
            'Original',
            $this->container->getByType(TeamService2::class)->findByPrimary($team->getPrimary())->name
        );
    }

    public function testEditOrganizer(): void
    {
        $this->loginUser(ContestGrantModel::EventManager);
        $team = $this->createTeam('Original', [$this->personA]);
        $data = [
            'team' => [
                'name' => 'Edited',
                'game_lang' => 'en',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_0_container' => self::personToValues($this->event->event_type->contest, $this->personA),
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
            'captcha' => 'pqrt',
            'privacy' => '1',
        ];
        /** @var RedirectResponse $response */
        $response = $this->createFormRequest('edit', $data, ['id' => $team->getPrimary()]);
        Assert::type(RedirectResponse::class, $response);
        Assert::contains('detail', $response->getUrl());
        Assert::same(
            'Edited',
            $this->container->getByType(TeamService2::class)->findByPrimary($team->getPrimary())->name
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
                'game_lang' => 'en',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_0_container' => self::personToValues($this->event->event_type->contest, $this->personA),
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
            'captcha' => 'pqrt',
            'privacy' => '1',
        ];
        Assert::exception(
            fn() => $this->createFormRequest('edit', $data, ['id' => $team->getPrimary()]),
            ForbiddenRequestException::class
        );
        Assert::same(
            'Original',
            $this->container->getByType(TeamService2::class)->findByPrimary($team->getPrimary())->name
        );
    }

    public function testEditOrganizerOutDate(): void
    {
        $this->outDateEvent();
        $this->loginUser(ContestGrantModel::EventManager);
        $team = $this->createTeam('Original', [$this->personA]);
        $data = [
            'team' => [
                'name' => 'Edited',
                'game_lang' => 'en',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_0_container' => self::personToValues($this->event->event_type->contest, $this->personA),
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
            'captcha' => 'pqrt',
            'privacy' => '1',
        ];
        /** @var RedirectResponse $response */
        $response = $this->createFormRequest('edit', $data, ['id' => $team->getPrimary()]);
        Assert::type(RedirectResponse::class, $response);
        Assert::contains('detail', $response->getUrl());
        Assert::same(
            'Edited',
            $this->container->getByType(TeamService2::class)->findByPrimary($team->getPrimary())->name
        );
    }

    public function testEditReplaceMember(): void
    {
        $this->loginUser(ContestGrantModel::EventManager);
        $team = $this->createTeam('Original', [$this->personA]);
        $data = [
            'team' => [
                'name' => 'Edited',
                'game_lang' => 'en',
            ],
            'member_0' => (string)$this->personB->person_id,
            'member_0_container' => self::personToValues($this->event->event_type->contest, $this->personB),
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
            'captcha' => 'pqrt',
            'privacy' => '1',
        ];
        $response = $this->createFormRequest('edit', $data, ['id' => $team->getPrimary()]);
        Assert::type(RedirectResponse::class, $response);
        /** @phpstan-var TeamModel2|null $newTeam */
        $newTeam = $this->container->getByType(TeamService2::class)->findByPrimary($team->getPrimary());
        Assert::same(1, $newTeam->getMembers()->count('*'));
        Assert::same($this->personB->person_id, $newTeam->getMembers()->fetch()->person_id);
    }

    public function testCreateDuplicateMember(): void
    {
        $before = $this->container->getByType(TeamService2::class)->getTable()->count('*');
        $data = [
            'team' => [
                'name' => 'Edited',
                'game_lang' => 'en',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_1' => (string)$this->personA->person_id,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
            'captcha' => 'pqrt',
            'privacy' => '1',
        ];
        $response = $this->createFormRequest('create', $data);
        Assert::type(TextResponse::class, $response);
        $after = $this->container->getByType(TeamService2::class)->getTable()->count('*');
        Assert::same($before, $after);
    }

    public function testCreateDuplicateMember2(): void
    {
        $this->createTeam('Unique', [$this->personA]);
        $before = $this->container->getByType(TeamService2::class)->getTable()->count('*');

        $data = [
            'team' => [
                'name' => 'Edited',
                'game_lang' => 'en',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
            'captcha' => 'pqrt',
            'privacy' => '1',
        ];
        $response = $this->createFormRequest('create', $data);
        Assert::type(TextResponse::class, $response);
        $after = $this->container->getByType(TeamService2::class)->getTable()->count('*');
        Assert::same($before, $after);
    }

    public function testEditDuplicateTeamName(): void
    {
        $this->createTeam('Unique', [$this->personB]);
        $before = $this->container->getByType(TeamService2::class)->getTable()->count('*');

        $data = [
            'team' => [
                'name' => 'Unique',
                'game_lang' => 'en',
            ],
            'member_0' => (string)$this->personA->person_id,
            'member_1' => null,
            'member_2' => null,
            'member_3' => null,
            'member_4' => null,
            'captcha' => 'pqrt',
            'privacy' => '1',
        ];
        $response = $this->createFormRequest('create', $data);
        Assert::type(TextResponse::class, $response);
        $after = $this->container->getByType(TeamService2::class)->getTable()->count('*');
        Assert::same($before, $after);
    }

    /**
     * @phpstan-param PersonModel[] $persons
     */
    protected function createTeam(string $name, array $persons): TeamModel2
    {
        /** @phpstan-var TeamModel2 $team */
        $team = $this->container->getByType(TeamService2::class)->storeModel([
            'name' => $name,
            'category' => 'B',
            'event_id' => $this->event->event_id,
        ]);
        foreach ($persons as $person) {
            $this->container->getByType(TeamMemberService::class)->storeModel([
                'person_id' => $person->person_id,
                'fyziklani_team_id' => $team->fyziklani_team_id,
            ]);
        }
        return $team;
    }
}

// phpcs:disable
$testCase = new FOLTeamApplicationPresenterTest($container);
$testCase->run();
// phpcs:enable
