<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\OrganizerModule\Stalking;

use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\ContestGrantService;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Models\ORM\Services\OrganizerService;
use FKSDB\Models\ORM\Services\PersonInfoService;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\Application\IPresenter;
use Nette\Application\Request;

abstract class StalkingTestCase extends DatabaseTestCase
{
    protected PersonModel $person;
    protected IPresenter $fixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->person = $this->container->getByType(PersonService::class)->storeModel([
            'family_name' => 'Testerovič',
            'other_name' => 'Tester',
            'born_family_name' => 'Travisový',
            'display_name' => 'Tester Githubový',
            'gender' => 'M',
        ]);
        $this->container->getByType(PersonInfoService::class)->storeModel([
            'person_id' => $this->person->person_id,
            'preferred_lang' => 'cs',
            'born' => '1989-11-17',
            'id_number' => 'ABCD1234',
            'born_id' => '891117/1234',
            'phone' => '+420123456789',
            'im' => null,
            'note' => null,
            'uk_login' => 'TESTER',
            'account' => null,
            'birthplace' => 'Testerove lazne',
            'citizenship' => 'CR',
            'health_insurance' => 111,
            'employer' => 'MFF UK',
            'academic_degree_prefix' => 'ts.',
            'academic_degree_suffix' => 'tr.',
            'email' => 'tester@example.com',
            'career' => '',
            'phone_parent_d' => '+10123456789',
            'phone_parent_m' => '+421123456789',
            'email_parent_d' => 'tester_d@example.com',
            'email_parent_m' => 'tester_m@example.com',
        ]);
        $userPerson = $this->container->getByType(PersonService::class)->storeModel([
            'family_name' => 'Cartesian',
            'other_name' => 'Cartesiansky',
            'gender' => 'M',
        ]);
        /** @var LoginModel $login */
        $login = $this->container->getByType(LoginService::class)->storeModel(
            ['person_id' => $userPerson->person_id, 'active' => 1]
        );
        $this->container->getByType(OrganizerService::class)->storeModel(
            ['person_id' => $userPerson->person_id, 'contest_id' => 1, 'since' => 1, 'order' => 1]
        );
        $this->container->getByType(ContestGrantService::class)->storeModel(
            ['login_id' => $login->login_id, 'role_id' => $this->getUserRoleId(), 'contest_id' => 1]
        );
        $this->fixture = $this->createPresenter('Organizer:Person');
        $this->authenticateLogin($login, $this->fixture);
    }

    abstract protected function getUserRoleId(): int;

    final protected function createRequest(): Request
    {
        return new Request('Organizer:Person', 'GET', [
            'action' => 'detail',
            'lang' => 'en',
            'contestId' => 1,
            'year' => 1,
            'series' => 1,
            'id' => $this->person->person_id,
        ]);
    }
}
