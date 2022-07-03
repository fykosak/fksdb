<?php

declare(strict_types=1);

namespace FKSDB\Tests\ModelsTests;

use FKSDB\Models\Authentication\PasswordAuthenticator;
use FKSDB\Models\Mail\MailTemplateFactory;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelLogin;
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
use FKSDB\Tests\MockEnvironment\MockApplication;
use FKSDB\Tests\MockEnvironment\MockPresenter;
use Nette\Application\IPresenterFactory;
use Nette\Application\UI\Presenter;
use Nette\Database\Explorer;
use Nette\DI\Container;
use Nette\Http\Session;
use Nette\Security\UserStorage;
use Tester\Environment;
use Tester\TestCase;

abstract class DatabaseTestCase extends TestCase
{
    private Container $container;
    protected Explorer $explorer;
    private int $instanceNo;
    protected ModelSchool $genericSchool;

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
        $this->genericSchool = $this->getContainer()->getByType(ServiceSchool::class)->createNewModel(
            ['name' => 'Skola', 'name_abbrev' => 'SK', 'address_id' => $address->address_id]
        );
        $serviceContestYear = $this->getContainer()->getByType(ServiceContestYear::class);
        $fykosData = [
            'contest_id' => ModelContest::ID_FYKOS,
            'year' => 1,
            'ac_year' => YearCalculator::getCurrentAcademicYear(),
        ];
        $vyfukData = [
            'contest_id' => ModelContest::ID_VYFUK,
            'year' => 1,
            'ac_year' => YearCalculator::getCurrentAcademicYear(),
        ];
        $serviceContestYear->storeModel(
            $fykosData,
            $serviceContestYear->getTable()->where($fykosData)->fetch()
        );
        $serviceContestYear->storeModel(
            $vyfukData,
            $serviceContestYear->getTable()->where($vyfukData)->fetch()
        );
    }

    protected function tearDown(): void
    {
        $tables = [
            DbNames::TAB_EMAIL_MESSAGE,
            DbNames::TAB_SUBMIT,
            DbNames::TAB_TASK,

            DbNames::TAB_FYZIKLANI_SUBMIT,
            DbNames::TAB_FYZIKLANI_TASK,

            DbNames::TAB_PERSON_SCHEDULE,
            DbNames::TAB_SCHEDULE_ITEM,
            DbNames::TAB_SCHEDULE_GROUP,

            DbNames::TAB_E_FYZIKLANI_PARTICIPANT,
            DbNames::TAB_EVENT_PARTICIPANT,
            DbNames::TAB_FYZIKLANI_TEAM_TEACHER,
            DbNames::TAB_FYZIKLANI_TEAM_MEMBER,
            DbNames::TAB_FYZIKLANI_TEAM,
            DbNames::TAB_E_FYZIKLANI_TEAM,
            DbNames::TAB_FYZIKLANI_GAME_SETUP,
            DbNames::TAB_EVENT_ORG,
            DbNames::TAB_EVENT,

            DbNames::TAB_ORG,
            DbNames::TAB_PERSON_HISTORY,
            DbNames::TAB_CONTESTANT_BASE,
            DbNames::TAB_CONTEST_YEAR,
            DbNames::TAB_SCHOOL,
            DbNames::TAB_ADDRESS,
            DbNames::TAB_AUTH_TOKEN,
            DbNames::TAB_LOGIN,
            DbNames::TAB_PERSON,
        ];
        foreach ($tables as $table) {
            $this->explorer->query("DELETE FROM `$table`");
        }
    }

    protected function createPerson(
        string $name,
        string $surname,
        ?array $info = null,
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

            $pseudoLogin = $this->getContainer()->getByType(ServiceLogin::class)->createNewModel($loginData);

            if (isset($pseudoLogin->hash)) {
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

    protected function mockApplication(): void
    {
        $mockPresenter = new MockPresenter();
        $application = new MockApplication($mockPresenter);
        $this->getContainer()->callInjects($mockPresenter);
        $mailFactory = $this->getContainer()->getByType(MailTemplateFactory::class);
        $mailFactory->injectApplication($application);
    }

    /**
     * @param $token
     * @param null $timeout
     */
    protected function fakeProtection($token, $timeout = null): void
    {
        /** @var Session $session */
        $session = $this->getContainer()->getService('session');
        $section = $session->getSection('Nette.Forms.Form/CSRF');
        $key = "key$timeout";
        $section->$key = $token;
    }

    protected function authenticateLogin(ModelLogin $login, ?Presenter $presenter = null): void
    {
        /** @var UserStorage $storage */
        $storage = $this->getContainer()->getByType(UserStorage::class);
        $storage->saveAuthentication($login);

        if ($presenter) {
            $presenter->getUser()->login($login);
        }
    }

    protected function authenticatePerson(ModelPerson $person, ?Presenter $presenter = null): void
    {
        $this->authenticateLogin($person->getLogin(), $presenter);
    }

    protected function createPresenter(string $presenterName): Presenter
    {
        $_COOKIE['_nss'] = '1';
        $presenterFactory = $this->getContainer()->getByType(IPresenterFactory::class);
        $presenter = $presenterFactory->createPresenter($presenterName);
        $presenter->autoCanonicalize = false;
        return $presenter;
    }
}
