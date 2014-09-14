<?php

use Nette\Database\Connection;
use Tester\Environment;
use Tester\TestCase;

abstract class DatabaseTestCase extends TestCase {

    /**
     * @var Connection
     */
    protected $connection;

    function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    protected function setUp() {
        Environment::lock(LOCK_DB, TEMP_DIR);
        $this->createPerson('Student', 'Pilný', null, 1);
        $this->connection->query("INSERT INTO address (address_id, target, city, region_id) VALUES(1, 'nikde', 'nicov', 3)");
        $this->connection->query("INSERT INTO school (school_id, address_id) VALUES(1, 1)");
        $this->connection->query("INSERT INTO contest_year (contest_id, year, ac_year) VALUES(1, 1, 2000)");
        $this->connection->query("INSERT INTO contest_year (contest_id, year, ac_year) VALUES(2, 1, 2000)");
        $this->createPersonHistory(1, 2000, 1, null, 1);
    }

    protected function tearDown() {
        $this->connection->query("DELETE FROM person_history");
        $this->connection->query("DELETE FROM contest_year");
        $this->connection->query("DELETE FROM school");
        $this->connection->query("DELETE FROM address");
        $this->connection->query("DELETE FROM person");
    }

    protected function createPerson($name, $surname, $email = null, $personId = null) {
        $this->connection->query("INSERT INTO person (person_id, other_name, family_name) VALUES(?, ?, ?)", $personId, $name, $surname);
        $personId = $this->connection->lastInsertId();

        if ($email) {
            $this->connection->query("INSERT INTO person_info (person_id, email) VALUES(?, ?)", $personId, $email);
        }

        return $personId;
    }

    protected function createPersonHistory($personId, $acYear, $school = null, $class = null, $studyYear = null) {
        $this->connection->query("INSERT INTO person_history (person_id, ac_year, school_id, class, study_year) VALUES(?, ?, ?, ?, ?)", $personId, $acYear, $school, $class, $studyYear);
        $personHistoryId = $this->connection->lastInsertId();


        return $personHistoryId;
    }

}
