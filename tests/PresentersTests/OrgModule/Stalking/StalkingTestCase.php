<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\OrgModule\Stalking;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Tests\MockEnvironment\MockApplicationTrait;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\Application\IPresenter;
use Nette\Application\Request;
use Nette\DI\Container;

/**
 * Class Stalking
 * @package Persons
 */
abstract class StalkingTestCase extends DatabaseTestCase
{
    use MockApplicationTrait;

    /** @var int */
    protected $personId;

    protected IPresenter $fixture;

    /**
     * Stalking constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->setContainer($container);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->personId = $this->insert(DbNames::TAB_PERSON, [
            'family_name' => 'Testerovič',
            'other_name' => 'Tester',
            'born_family_name' => 'Travisový',
            'display_name' => 'Tester Githubový',
            'gender' => 'M',
        ]);
        $this->insert(DbNames::TAB_PERSON_INFO, [
            'person_id' => $this->personId,
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

        $userPersonId = $this->insert(DbNames::TAB_PERSON, [
            'family_name' => 'Cartesian',
            'other_name' => 'Cartesiansky',
            'gender' => 'M',
        ]);

        $loginId = $this->insert(DbNames::TAB_LOGIN, ['person_id' => $userPersonId, 'active' => 1]);
        $this->insert(DbNames::TAB_ORG, ['person_id' => $userPersonId, 'contest_id' => 1, 'since' => 1, 'order' => 1]);
        $this->insert(
            DbNames::TAB_GRANT,
            ['login_id' => $loginId, 'role_id' => $this->getUserRoleId(), 'contest_id' => 1]
        );
        $this->fixture = $this->createPresenter('Org:Person');
        $this->authenticate($loginId, $this->fixture);
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
            'id' => $this->personId,
        ]);
    }
}
