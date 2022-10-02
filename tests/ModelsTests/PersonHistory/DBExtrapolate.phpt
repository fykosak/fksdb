<?php

declare(strict_types=1);

namespace FKSDB\Tests\ModelsTests\PersonHistory;

// phpcs:disable
$container = require '../../Bootstrap.php';

// phpcs:enable
use FKSDB\Models\ORM\Services\ContestYearService;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\DI\Container;
use Tester\Assert;

class DBExtrapolate extends DatabaseTestCase
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    public function testNull(): void
    {
        $person = $this->createPerson('Student', 'Pilný');
        $this->createPersonHistory($person, ContestYearService::getCurrentAcademicYear(), $this->genericSchool, 1);

        $extrapolated = $person->getHistory(ContestYearService::getCurrentAcademicYear() + 1, true);

        Assert::same(ContestYearService::getCurrentAcademicYear() + 1, $extrapolated->ac_year);
        Assert::same($this->genericSchool->school_id, $extrapolated->school_id);
        Assert::same(null, $extrapolated->class);
        Assert::same(2, $extrapolated->study_year);
    }
}

// phpcs:disable
$testCase = new DBExtrapolate($container);
$testCase->run();
// phpcs:enable
