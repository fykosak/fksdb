<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter;

$container = require '../../../Bootstrap.php';

use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Tests\Events\EventTestCase;
use Nette\Application\IPresenter;
use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Template;
use Nette\Utils\DateTime;
use Tester\Assert;

class ApplicationPresenterTest extends EventTestCase
{

    private IPresenter $fixture;

    protected function getEventId(): int
    {
        return 0;
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
        $eventId = $this->createEvent([
            'event_type_id' => 2,
            'event_year' => 19,
            'registration_begin' => DateTime::from(time() + DateTime::DAY),
        ]);
        Assert::exception(function () use ($eventId): void {
            $request = new Request('Public:Register', 'GET', [
                'action' => 'default',
                'lang' => 'en',
                'id' => 666,
                'eventId' => $eventId,
                'contestId' => 1,
                'year' => 1,
            ]);

            $this->fixture->run($request);
        }, NotFoundException::class, 'Unknown application.', 404);
    }

    public function testClosed(): void
    {
        $eventId = $this->createEvent([
            'event_type_id' => 2,
            'event_year' => 20,
            'registration_begin' => DateTime::from(time() + DateTime::DAY),
        ]);

        $request = new Request('Public:Application', 'GET', [
            'action' => 'default',
            'lang' => 'en',
            'contestId' => 1,
            'year' => 1,
            'eventId' => $eventId,
        ]);

        $response = $this->fixture->run($request);
        Assert::type(TextResponse::class, $response);
        /** @var TextResponse $response */
        $source = $response->getSource();
        Assert::type(Template::class, $source);

        $html = (string)$source;
        Assert::contains('Registration is not open.', $html);
    }
}

$testCase = new ApplicationPresenterTest($container);
$testCase->run();
