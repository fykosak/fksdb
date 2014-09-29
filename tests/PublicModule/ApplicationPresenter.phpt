<?php

$container = require '../bootstrap.php';

use Events\EventTestCase;
use Nette\Application\Request;
use Nette\DateTime;
use Nette\DI\Container;
use PublicModule\ApplicationPresenter;
use Tester\Assert;

class ApplicationPresenterTest extends EventTestCase {

    /**
     * @var ApplicationPresenter
     */
    private $fixture;

    function __construct(Container $container) {
        parent::__construct($container);
        $this->setContainer($container);
    }

    protected function setUp() {
        parent::setUp();
        $this->fixture = $this->createPresenter('Public:Application');
    }

    public function test404() {
        $fixture = $this->fixture;
        Assert::exception(function() use ($fixture) {
                    $request = new Request('Public:Register', 'GET', array(
                        'action' => 'default',
                        'lang' => 'cs',
                        'eventId' => 666,
                    ));

                    $fixture->run($request);
                }, 'Nette\Application\BadRequestException', 'Neexistující akce.', 404);
    }

    public function test404Application() {
        $fixture = $this->fixture;
        $eventId = $this->createEvent(array(
            'event_type_id' => 2,
            'event_year' => 19,
            'registration_begin' => DateTime::from(time() + DateTime::DAY),
        ));
        Assert::exception(function() use ($fixture, $eventId) {
                    $request = new Request('Public:Register', 'GET', array(
                        'action' => 'default',
                        'lang' => 'cs',
                        'id' => 666,
                        'eventId' => $eventId,
                        'contestId' => 1,
                        'year' => 1,
                    ));

                    $fixture->run($request);
                }, 'Nette\Application\BadRequestException', 'Neexistující přihláška.', 404);
    }

    public function testClosed() {
        $eventId = $this->createEvent(array(
            'event_type_id' => 2,
            'event_year' => 20,
            'registration_begin' => DateTime::from(time() + DateTime::DAY),
        ));

        $request = new Request('Public:Application', 'GET', array(
            'action' => 'default',
            'lang' => 'cs',
            'contestId' => 1,
            'year' => 1,
            'eventId' => $eventId,
        ));

        $response = $this->fixture->run($request);
        Assert::type('Nette\Application\Responses\TextResponse', $response);

        $source = $response->getSource();
        Assert::type('Nette\Templating\ITemplate', $source);

        $html = (string) $source;
        Assert::contains('Přihlašování není povoleno', $html);
    }

}

$testCase = new ApplicationPresenterTest($container);
$testCase->run();
