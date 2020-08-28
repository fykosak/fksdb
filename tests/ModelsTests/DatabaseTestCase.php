<?php

namespace FKSDB\Tests\ModelTests;

use FKSDB\Authentication\PasswordAuthenticator;
use FKSDB\ORM\DbNames;
use Nette\Database\Connection;
use Nette\Database\Context;
use Nette\Database\Row;
use Nette\DI\Container;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

abstract class DatabaseTestCase extends TestCase {

    private Container $container;

    protected Connection $connection;

    private int $instanceNo;

    /**
     * DatabaseTestCase constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        $this->container = $container;
        /** @var Context $context */
        $context = $container->getByType(Context::class);
        $this->connection = $context->getConnection();
        $max = $container->parameters['tester']['dbInstances'];
        $this->instanceNo = (getmypid() % $max) + 1;
        $this->connection->query('USE fksdb_test' . $this->instanceNo);
    }

    protected function getContainer(): Container {
        return $this->container;
    }

    protected function setUp(): void {
        Environment::lock(LOCK_DB . $this->instanceNo, TEMP_DIR);
        $this->connection->query("INSERT INTO address (address_id, target, city, region_id) VALUES(1, 'nikde', 'nicov', 3)");
        $this->connection->query("INSERT INTO school (school_id, name, name_abbrev, address_id) VALUES(1, 'Skola', 'SK', 1)");
        $this->connection->query("INSERT INTO contest_year (contest_id, year, ac_year) VALUES(1, 1, 2000)");
        $this->connection->query("INSERT INTO contest_year (contest_id, year, ac_year) VALUES(2, 1, 2000)");
    }

    protected function tearDown(): void {
        $this->connection->query('DELETE FROM org');
        $this->connection->query('DELETE FROM global_session');
        $this->connection->query('DELETE FROM login');
        $this->connection->query('DELETE FROM person_history');
        $this->connection->query('DELETE FROM contest_year');
        $this->connection->query('DELETE FROM school');
        $this->connection->query('DELETE FROM address');
        $this->connection->query('DELETE FROM person');
    }

    protected function createPerson(string $name, string $surname, array $info = [], ?array $loginData = null): int {
        $this->connection->query("INSERT INTO person (other_name, family_name,gender) VALUES(?, ?,'M')", $name, $surname);
        $personId = $this->connection->getInsertId();

        if ($info) {
            $info['person_id'] = $personId;
            $this->insert(DbNames::TAB_PERSON_INFO, $info);
        }

        if (!is_null($loginData)) {
            $data = [
                'login_id' => $personId,
                'person_id' => $personId,
                'active' => 1,
            ];
            $loginData = array_merge($data, $loginData);

            $this->insert(DbNames::TAB_LOGIN, $loginData);

            if (isset($loginData['hash'])) {
                $pseudoLogin = (object)$loginData;
                $hash = PasswordAuthenticator::calculateHash($loginData['hash'], $pseudoLogin);
                $this->connection->query("UPDATE login SET `hash` = ? WHERE person_id = ?", $hash, $personId);
            }
        }

        return $personId;
    }

    protected function assertPersonInfo(int $personId): Row {
        $personInfo = $this->connection->fetch('SELECT * FROM person_info WHERE person_id = ?', $personId);
        Assert::notEqual(false, $personInfo);
        return $personInfo;
    }

    protected function createPersonHistory(int $personId, int $acYear, ?int $school = null, ?int $studyYear = null, ?string $class = null): int {
        $this->connection->query("INSERT INTO person_history (person_id, ac_year, school_id, class, study_year) VALUES(?, ?, ?, ?, ?)", $personId, $acYear, $school, $class, $studyYear);
        return $this->connection->getInsertId();
    }

    protected function insert(string $table, array $data): int {
        $this->connection->query("INSERT INTO `$table`", $data);
        return $this->connection->getInsertId();
    }

}
