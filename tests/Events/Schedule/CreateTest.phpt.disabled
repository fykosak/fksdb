<?php

declare(strict_types=1);

namespace FKSDB\Tests\Events\Schedule;

// phpcs:disable
$container = require '../../Bootstrap.php';

// phpcs:enable

use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use Nette\Application\Responses\RedirectResponse;
use Tester\Assert;

class CreateTest extends ScheduleTestCase
{

    public function testRegistration(): void
    {
        $request = $this->createAccommodationRequest();

        $response = $this->fixture->run($request);
        Assert::type(RedirectResponse::class, $response);

        Assert::equal(
            3,
            $this->getContainer()
                ->getByType(PersonScheduleService::class)
                ->getTable()
                ->where(['schedule_item_id' => $this->item->schedule_item_id])
                ->count('*')
        );
    }

    public function getAccommodationCapacity(): int
    {
        return 3;
    }
}

// phpcs:disable
$testCase = new CreateTest($container);
$testCase->run();
// phpcs:enable
