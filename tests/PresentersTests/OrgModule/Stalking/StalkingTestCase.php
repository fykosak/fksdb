<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\OrgModule\Stalking;

use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\GrantService;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Models\ORM\Services\OrgService;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\ORM\Services\PersonInfoService;
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
        $this->person = $this->getContainer()->getByType(PersonService::class)->createNewModel([
            'family_name' => 'Testerovič',
            'other_name' => 'Tester',
            'born_family_name' => 'Travisový',
            'display_name' => 'Tester Githubový',
            'gender' => 'M',
        ]);
        $this->getContainer()->getByType(PersonInfoService::class)->createNewModel([
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
        $userPerson = $this->getContainer()->getByType(PersonService::class)->createNewModel([
            'family_name' => 'Cartesian',
            'other_name' => 'Cartesiansky',
            'gender' => 'M',
        ]);

        $login = $this->getContainer()->getByType(LoginService::class)->createNewModel(
            ['person_id' => $userPerson->person_id, 'active' => 1]
        );
        $this->getContainer()->getByType(OrgService::class)->createNewModel(
            ['person_id' => $userPerson->person_id, 'contest_id' => 1, 'since' => 1, 'order' => 1]
        );
        $this->getContainer()->getByType(GrantService::class)->createNewModel(
            ['login_id' => $login->login_id, 'role_id' => $this->getUserRoleId(), 'contest_id' => 1]
        );
        $this->fixture = $this->createPresenter('Org:Person');
        $this->authenticateLogin($login, $this->fixture);
    }

    abstract protected function getUserRoleId(): int;

    final protected function createRequest(): Request
    {
        return new Request('Org:Person', 'GET', [
            'action' => 'detail',
            'lang' => 'en',
            'contestId' => 1,
            'year' => 1,
            'series' => 1,
            'id' => $this->person->person_id,
        ]);
    }
}
