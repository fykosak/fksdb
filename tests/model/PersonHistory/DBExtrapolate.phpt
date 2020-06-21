<?php
namespace FKSDB\Tests\PersonHistory;

$container = require '../../bootstrap.php';

use FKSDB\ORM\Services\ServicePerson;
use FKSDB\Tests\DatabaseTestCase;
use Nette\DI\Container;
use Tester\Assert;

class DBExtrapolate extends DatabaseTestCase {

    /**
     * @var ServicePerson
     */
    private $service;

    /**
     * ModelPersonHistoryTest constructor.
     * @param ServicePerson $service
     * @param Container $container
     */
    public function __construct(ServicePerson $service, Container $container) {
        parent::__construct($container);
        $this->service = $service;
    }

    public function testNull() {
        $personId = $this->createPerson('Student', 'Pilný');
        $this->createPersonHistory($personId, 2000, 1, 1);

        $person = $this->service->findByPrimary($personId);
        $extrapolated = $person->getHistory(2001, true);

        Assert::same(2001, $extrapolated->ac_year);
        Assert::same(1, $extrapolated->school_id);
        Assert::same(null, $extrapolated->class);
        Assert::same(2, $extrapolated->study_year);
    }
}

$testCase = new DBExtrapolate($container->getByType(ServicePerson::class), $container);
$testCase->run();
