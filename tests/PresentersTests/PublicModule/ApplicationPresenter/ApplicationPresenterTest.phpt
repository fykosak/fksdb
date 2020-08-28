<?php

namespace FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter;

$container = require '../../../bootstrap.php';

use FKSDB\Tests\Events\EventTestCase;
use Nette\Application\BadRequestException;
use Nette\Application\IPresenter;
use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\ITemplate;
use Nette\Utils\DateTime;
use Tester\Assert;

class ApplicationPresenterTest extends EventTestCase {

    private IPresenter $fixture;

    protected function getEventId(): int {
        return 0;
    }

    protected function setUp(): void {
        parent::setUp();
        $this->fixture = $this->createPresenter('Public:Application');
    }

    public function test404(): void {
        $fixture = $this->fixture;
        Assert::exception(function () use ($fixture) {
            $request = new Request('Public:Register', 'GET', [
                'action' => 'default',
                'lang' => 'cs',
                'eventId' => 666,
            ]);

            $fixture->run($request);
        }, BadRequestException::class, 'Neexistující akce.', 404);
    }

    public function test404Application(): void {
        $fixture = $this->fixture;
        $eventId = $this->createEvent([
            'event_type_id' => 2,
            'event_year' => 19,
            'registration_begin' => DateTime::from(time() + DateTime::DAY),
        ]);
        Assert::exception(function () use ($fixture, $eventId) {
            $request = new Request('Public:Register', 'GET', [
                'action' => 'default',
                'lang' => 'cs',
                'id' => 666,
                'eventId' => $eventId,
                'contestId' => 1,
                'year' => 1,
            ]);

            $fixture->run($request);
        }, BadRequestException::class, 'Neexistující přihláška.', 404);
    }

    public function testClosed(): void {
        $eventId = $this->createEvent([
            'event_type_id' => 2,
            'event_year' => 20,
            'registration_begin' => DateTime::from(time() + DateTime::DAY),
        ]);

        $request = new Request('Public:Application', 'GET', [
            'action' => 'default',
            'lang' => 'cs',
            'contestId' => 1,
            'year' => 1,
            'eventId' => $eventId,
        ]);

        $response = $this->fixture->run($request);
        Assert::type(TextResponse::class, $response);

        $source = $response->getSource();
        Assert::type(ITemplate::class, $source);

        $html = (string)$source;
        Assert::contains('Přihlašování není povoleno', $html);
    }
}

$testCase = new ApplicationPresenterTest($container);
$testCase->run();
