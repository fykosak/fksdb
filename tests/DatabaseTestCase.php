<?php

use Nette\Database\Connection;
use Nette\DI\Container;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

abstract class DatabaseTestCase extends TestCase {

    /**
     * @var Connection
     */
    protected $connection;
    private $instanceNo;

    function __construct(Container $container) {
        $this->connection = $container->getService('nette.database.default');
        $max = $container->parameters['tester']['dbInstances'];
        $this->instanceNo = (getmypid() % $max) + 1;
        $this->connection->exec('USE fksdb_test' . $this->instanceNo);
    }

    protected function setUp() {
        Environment::lock(LOCK_DB . $this->instanceNo, TEMP_DIR);
        $this->connection->query("INSERT INTO address (address_id, target, city, region_id) VALUES(1, 'nikde', 'nicov', 3)");
        $this->connection->query("INSERT INTO school (school_id, address_id) VALUES(1, 1)");
        $this->connection->query("INSERT INTO contest_year (contest_id, year, ac_year) VALUES(1, 1, 2000)");
        $this->connection->query("INSERT INTO contest_year (contest_id, year, ac_year) VALUES(2, 1, 2000)");
    }

    protected function tearDown() {
        $this->connection->query("DELETE FROM global_session");
        $this->connection->query("DELETE FROM login");
        $this->connection->query("DELETE FROM person_history");
        $this->connection->query("DELETE FROM contest_year");
        $this->connection->query("DELETE FROM school");
        $this->connection->query("DELETE FROM address");
        $this->connection->query("DELETE FROM person");
    }

    protected function createPerson($name, $surname, $info = array(), $withLogin = false) {
        $this->connection->query("INSERT INTO person (other_name, family_name) VALUES(?, ?)", $name, $surname);
        $personId = $this->connection->lastInsertId();

        if ($info) {
            $info['person_id'] = $personId;
            $this->connection->query("INSERT INTO person_info", $info);
        }

        if ($withLogin) {
            $login = array(
                'login_id' => $personId,
                'person_id' => $personId,
                'active' => 1
            );
            $this->connection->query("INSERT INTO login", $login);
        }

        return $personId;
    }

    protected function assertPersonInfo($personId) {
        $personInfo = $this->connection->fetch('SELECT * FROM person_info WHERE person_id = ?', $personId);
        Assert::notEqual(false, $personInfo);
        return $personInfo;
    }

    protected function createPersonHistory($personId, $acYear, $school = null, $studyYear = null, $class = null) {
        $this->connection->query("INSERT INTO person_history (person_id, ac_year, school_id, class, study_year) VALUES(?, ?, ?, ?, ?)", $personId, $acYear, $school, $class, $studyYear);
        $personHistoryId = $this->connection->lastInsertId();


        return $personHistoryId;
    }

    protected function insert($table, $data) {
        $this->connection->query("INSERT INTO `$table`", $data);
        return $this->connection->lastInsertId();
    }

}
