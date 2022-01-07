<?php

declare(strict_types=1);

namespace FKSDB\Tests\ModelsTests;

use FKSDB\Models\Authentication\PasswordAuthenticator;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Models\ModelPersonHistory;
use FKSDB\Models\ORM\Models\ModelPersonInfo;
use FKSDB\Models\ORM\Models\ModelSchool;
use FKSDB\Models\ORM\Services\ServiceAddress;
use FKSDB\Models\ORM\Services\ServiceContestYear;
use FKSDB\Models\ORM\Services\ServiceLogin;
use FKSDB\Models\ORM\Services\ServicePerson;
use FKSDB\Models\ORM\Services\ServicePersonHistory;
use FKSDB\Models\ORM\Services\ServicePersonInfo;
use FKSDB\Models\ORM\Services\ServiceSchool;
use FKSDB\Models\YearCalculator;
use Nette\Database\Explorer;
use Nette\DI\Container;
use Tester\Environment;
use Tester\TestCase;

abstract class DatabaseTestCase extends TestCase
{

    private Container $container;
    protected Explorer $explorer;
    private int $instanceNo;

    protected ModelSchool $genericSchool;

    /**
     * DatabaseTestCase constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->explorer = $container->getByType(Explorer::class);
        $max = $container->parameters['tester']['dbInstances'];
        $this->instanceNo = (getmypid() % $max) + 1;
        $this->explorer->query('USE fksdb_test' . $this->instanceNo);
    }

    protected function getContainer(): Container
    {
        return $this->container;
    }

    protected function setUp(): void
    {
        Environment::lock(LOCK_DB . $this->instanceNo, TEMP_DIR);
        $address = $this->getContainer()->getByType(ServiceAddress::class)->createNewModel(
            ['target' => 'nikde', 'city' => 'nicov', 'region_id' => 3]
        );
        $this->genericSchool = $this->container->getByType(ServiceSchool::class)->createNewModel(
            ['name' => 'Skola', 'name_abbrev' => 'SK', 'address_id' => $address->address_id]
        );
        $this->getContainer()->getByType(ServiceContestYear::class)->createNewModel(
            ['contest_id' => ModelContest::ID_FYKOS, 'year' => 1, 'ac_year' => YearCalculator::getCurrentAcademicYear()]
        );
        $this->getContainer()->getByType(ServiceContestYear::class)->createNewModel(
            ['contest_id' => ModelContest::ID_VYFUK, 'year' => 1, 'ac_year' => YearCalculator::getCurrentAcademicYear()]
        );
    }

    protected function tearDown(): void
    {
        $this->truncateTables([
            DbNames::TAB_ORG,
            DbNames::TAB_LOGIN,
            DbNames::TAB_PERSON_HISTORY,
            DbNames::TAB_CONTEST_YEAR,
            DbNames::TAB_SCHOOL,
            DbNames::TAB_ADDRESS,
            DbNames::TAB_PERSON,
        ]);
    }

    protected function createPerson(
        string $name,
        string $surname,
        array $info = [],
        ?array $loginData = null
    ): ModelPerson {
        $person = $this->getContainer()->getByType(ServicePerson::class)->createNewModel(
            ['other_name' => $name, 'family_name' => $surname, 'gender' => 'M']
        );

        if ($info) {
            $info['person_id'] = $person->person_id;
            $this->getContainer()->getByType(ServicePersonInfo::class)->createNewModel($info);
        }

        if (!is_null($loginData)) {
            $data = [
                'login_id' => $person->person_id,
                'person_id' => $person->person_id,
                'active' => 1,
            ];
            $loginData = array_merge($data, $loginData);

            $this->getContainer()->getByType(ServiceLogin::class)->createNewModel($loginData);

            if (isset($loginData['hash'])) {
                // TODO
                $pseudoLogin = (object)$loginData;
                $hash = PasswordAuthenticator::calculateHash($loginData['hash'], $pseudoLogin);
                $this->explorer->query('UPDATE login SET `hash` = ? WHERE person_id = ?', $hash, $person->person_id);
            }
        }

        return $person;
    }

    protected function assertPersonInfo(ModelPerson $person): ModelPersonInfo
    {
        return $person->getInfo();
    }

    protected function createPersonHistory(
        ModelPerson $person,
        int $acYear,
        ?ModelSchool $school = null,
        ?int $studyYear = null,
        ?string $class = null
    ): ModelPersonHistory {
        return $this->getContainer()->getByType(ServicePersonHistory::class)->createNewModel([
            'person_id' => $person->person_id,
            'ac_year' => $acYear,
            'school_id' => $school ? $school->school_id : null,
            'class' => $class,
            'study_year' => $studyYear,
        ]);
    }

    protected function truncateTables(array $tables): void
    {
        foreach ($tables as $table) {
            $this->explorer->query("DELETE FROM `$table`");
        }
    }
}
