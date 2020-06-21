<?php

$container = require '../bootstrap.php';

use FKSDB\WebService\WebServiceModel;
use Nette\DI\Container;
use Tester\Assert;

class WebServiceModelTest extends DatabaseTestCase {

    /**
     * @var Container
     */
    private $container;

    /**
     * @var WebServiceModel
     */
    private $fixture;

    // /** @var int */
    //  private $personId;

    /**
     * WebServiceModelTest constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->container = $container;
    }

    protected function setUp() {
        parent::setUp();

        $this->fixture = $this->container->getService('webServiceModel');
        $this->person = $this->createPerson('Homer', 'Simpson', [], ['login' => 'homer', 'hash' => '123456']);
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    protected function tearDown() {
        parent::tearDown();
    }

    public function testResults() {
        $header = [
            'username' => 'homer',
            'password' => '123456',
        ];

        $this->fixture->authenticationCredentials((object)$header);

        $resultsReq = [
            'contest' => 'fykos',
            'year' => 1,
            'brojure' => '1 2 3 4 5 6',
        ];
        $result = $this->fixture->getResults((object)$resultsReq);

        Assert::type(SoapVar::class, $result);
    }

}

$testCase = new WebServiceModelTest($container);
$testCase->run();
