<?php

declare(strict_types=1);

namespace FKSDB\Tests\Events\Schedule;

use Nette\Application\Responses\RedirectResponse;
use Tester\Assert;

$container = require '../../Bootstrap.php';

class CreateTest extends ScheduleTestCase
{

    public function testRegistration(): void
    {
        $request = $this->createAccommodationRequest();

        $response = $this->fixture->run($request);
        Assert::type(RedirectResponse::class, $response);

        Assert::equal(
            3,
            (int)$this->explorer->fetchField(
                'SELECT count(*) FROM person_schedule WHERE schedule_item_id = ?',
                $this->itemId
            )
        );
    }

    public function getAccommodationCapacity(): int
    {
        return 3;
    }
}

$testCase = new CreateTest($container);
$testCase->run();
