<?php

$container = require '../bootstrap.php';

use Nette\Application\Request;
use Nette\DI\Container;
use PublicModule\RegisterPresenter;
use Tester\Assert;

class RegisterPresenterTest extends DatabaseTestCase {

    /**
     * @var Container
     */
    private $container;

    /**
     * @var RegisterPresenter
     */
    private $fixture;

    function __construct(Container $container) {
        parent::__construct($container);
        $this->container = $container;
    }

    protected function setUp() {
        parent::setUp();

        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $this->fixture = $presenterFactory->createPresenter('Public:Register');
        $this->fixture->autoCanonicalize = false;

        $this->container->getByType('Authentication\LoginUserStorage')->setPresenter($this->fixture);
    }

    public function testDispatch() {
        $request = new Request('Public:Register', 'GET', array(
            'action' => 'contestant',
            'lang' => 'cs',
        ));

        $response = $this->fixture->run($request);
        Assert::type('Nette\Application\Responses\TextResponse', $response);

        $source = $response->getSource();
        Assert::type('Nette\Templating\ITemplate', $source);

        $html = (string) $source;
        Assert::contains('Zvolit seminář', $html);
    }

    public function testForm() {
        $request = new Request('Public:Register', 'GET', array(
            'action' => 'contestant',
            'contestId' => 1,
            'lang' => 'cs',
        ));

        $response = $this->fixture->run($request);
        Assert::type('Nette\Application\Responses\TextResponse', $response);

        $source = $response->getSource();
        Assert::type('Nette\Templating\ITemplate', $source);

        $html = $source->__toString(true);
        Assert::contains('registrace řešitele', $html);
    }

}

$testCase = new RegisterPresenterTest($container);
$testCase->run();
