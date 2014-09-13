<?php

$container = require 'bootstrap.php';

use Nette\Database\Connection;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

class ModelPersonHistoryTest extends TestCase {

    /**
     * @var ServicePerson
     */
    private $service;

    /**
     * @var Connection
     */
    private $connection;

    function __construct(ServicePerson $service, Connection $connection) {
        $this->service = $service;
        $this->connection = $connection;
    }

    protected function setUp() {
        Environment::lock(LOCK_DB, TEMP_DIR);
        $this->connection->query("INSERT INTO person (person_id, other_name, family_name) VALUES(1, 'Student', 'Pilný')");
        $this->connection->query("INSERT INTO address (address_id, target, city, region_id) VALUES(1, 'nikde', 'nicov', 3)");
        $this->connection->query("INSERT INTO school (school_id, address_id) VALUES(1, 1)");
        $this->connection->query("INSERT INTO contest (contest_id, name) VALUES(3, 'Test')");
        $this->connection->query("INSERT INTO contest_year (contest_id, year, ac_year) VALUES(3, 1, 2000)");
        $this->connection->query("INSERT INTO person_history (person_id, ac_year, school_id, class, study_year) VALUES(1, 2000, 1, null, 1)");
    }

    protected function tearDown() {
        $this->connection->query("DELETE FROM person_history");
        $this->connection->query("DELETE FROM contest_year");
        $this->connection->query("DELETE FROM contest");
        $this->connection->query("DELETE FROM school");
        $this->connection->query("DELETE FROM address");
        $this->connection->query("DELETE FROM person");
    }

    public function testNull() {
        $person = $this->service->findByPrimary(1);
        $extrapolated = $person->getHistory(2001, true);

        Assert::same(2001, $extrapolated->ac_year);
        Assert::same(1, $extrapolated->school_id);
        Assert::same(null, $extrapolated->class);
        Assert::same(2, $extrapolated->study_year);
    }

}

$testCase = new ModelPersonHistoryTest($container->getService('ServicePerson'), $container->getService('nette.database.default'));
$testCase->run();
