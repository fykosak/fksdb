<?php

declare(strict_types=1);

namespace FKSDB\Tests\ModelsTests\PersonHistory;

/** @var Container $container */
$container = require '../../Bootstrap.php';

use FKSDB\Models\ORM\Services\ServicePerson;
use FKSDB\Models\YearCalculator;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\DI\Container;
use Tester\Assert;

class DBExtrapolate extends DatabaseTestCase
{

    private ServicePerson $service;

    /**
     * ModelPersonHistoryTest constructor.
     * @param ServicePerson $service
     * @param Container $container
     */
    public function __construct(ServicePerson $service, Container $container)
    {
        parent::__construct($container);
        $this->service = $service;
    }

    public function testNull(): void
    {
        $personId = $this->createPerson('Student', 'Pilný');
        $this->createPersonHistory($personId, YearCalculator::getCurrentAcademicYear(), 1, 1);

        $person = $this->service->findByPrimary($personId);
        $extrapolated = $person->getHistory(YearCalculator::getCurrentAcademicYear() + 1, true);

        Assert::same(YearCalculator::getCurrentAcademicYear() + 1, $extrapolated->ac_year);
        Assert::same(1, $extrapolated->school_id);
        Assert::same(null, $extrapolated->class);
        Assert::same(2, $extrapolated->study_year);
    }
}

$testCase = new DBExtrapolate($container->getByType(ServicePerson::class), $container);
$testCase->run();
