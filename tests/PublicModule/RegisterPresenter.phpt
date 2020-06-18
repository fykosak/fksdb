<?php

$container = require '../bootstrap.php';

use Authentication\LoginUserStorage;
use Nette\Application\IPresenterFactory;
use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\DI\Container;
use Nette\Application\UI\ITemplate;
use FKSDB\Modules\PublicModule\RegisterPresenter;
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

        $presenterFactory = $this->container->getByType(IPresenterFactory::class);
        $this->fixture = $presenterFactory->createPresenter('Public:Register');
        $this->fixture->autoCanonicalize = false;

        $this->container->getByType(LoginUserStorage::class)->setPresenter($this->fixture);
    }

    public function testDispatch() {
        $request = new Request('Public:Register', 'GET', [
            'action' => 'contest',
            'lang' => 'cs',
        ]);

        $response = $this->fixture->run($request);
        Assert::type(TextResponse::class, $response);

        $source = $response->getSource();
        Assert::type(ITemplate::class, $source);

        $html = (string)$source;
        Assert::contains('Zvolit seminář', $html);
    }

    public function testForm() {
        $request = new Request('Public:Register', 'GET', [
            'action' => 'contestant',
            'contestId' => 1,
            'year' => 1,
            'lang' => 'cs',
        ]);

        $response = $this->fixture->run($request);
        Assert::type(TextResponse::class, $response);

        $source = $response->getSource();
        Assert::type(ITemplate::class, $source);

        $html = $source->__toString(true);
        Assert::contains('registrace řešitele', $html);
    }

}

$testCase = new RegisterPresenterTest($container);
$testCase->run();
