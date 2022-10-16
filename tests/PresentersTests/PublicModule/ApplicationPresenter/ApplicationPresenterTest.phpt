<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter;

// phpcs:disable
$container = require '../../../Bootstrap.php';

// phpcs:enable
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Tests\Events\EventTestCase;
use Nette\Application\BadRequestException;
use Nette\Application\IPresenter;
use Nette\Application\Request;
use Nette\Utils\DateTime;
use Tester\Assert;

class ApplicationPresenterTest extends EventTestCase
{
    private IPresenter $fixture;

    protected function getEvent(): EventModel
    {
        throw new BadRequestException();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixture = $this->createPresenter('Public:Application');
    }

    public function test404(): void
    {
        Assert::exception(function (): void {
            $request = new Request('Public:Register', 'GET', [
                'action' => 'default',
                'lang' => 'cs',
                'eventId' => 666,
            ]);

            $this->fixture->run($request);
        }, EventNotFoundException::class, 'Event not found.', 404);
    }

    public function test404Application(): void
    {
        $event = $this->createEvent([
            'event_type_id' => 2,
            'event_year' => 19,
            'registration_begin' => DateTime::from(time() - DateTime::DAY),
            'registration_end' => DateTime::from(time() + DateTime::DAY),
        ]);
        Assert::exception(function () use ($event): void {
            $request = new Request('Public:Register', 'GET', [
                'action' => 'default',
                'lang' => 'en',
                'id' => 666,
                'eventId' => $event->event_id,
                'contestId' => 1,
                'year' => 1,
            ]);

            $this->fixture->run($request);
        }, NotFoundException::class, 'Unknown application.', 404);
    }

    public function testClosed(): void
    {
        $event = $this->createEvent([
            'event_type_id' => 2,
            'event_year' => 20,
            'registration_end' => DateTime::from(time() + DateTime::DAY),
            'registration_begin' => DateTime::from(time() + DateTime::DAY),
        ]);

        $request = new Request('Public:Application', 'GET', [
            'action' => 'default',
            'lang' => 'en',
            'contestId' => 1,
            'year' => 1,
            'eventId' => $event->event_id,
        ]);
        Assert::exception(fn() => $this->fixture->run($request), GoneException::class);
    }
}

// phpcs:disable
$testCase = new ApplicationPresenterTest($container);
$testCase->run();
// phpcs:enable
