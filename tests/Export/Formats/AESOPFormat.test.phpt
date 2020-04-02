<?php

$container = require '../../bootstrap.php';

use Exports\ExportFormatFactory;
use Exports\Formats\AESOPFormat;
use Exports\StoredQueryPostProcessing;
use FKSDB\CoreModule\ISeriesPresenter;
use Nette\DI\Container;
use Tester\Assert;

class AESOPFormatTest extends DatabaseTestCase {

    /**
     * @var Container
     */
    private $container;

    /**
     * @var AESOPFormat
     */
    private $fixture;

    function __construct(Container $container) {
        parent::__construct($container);
        $this->container = $container;
    }

    protected function setUp() {
        parent::setUp();

        $exportFactory = $this->container->getByType('Exports\ExportFormatFactory');
        /** @var \Exports\StoredQueryFactory $queryFactory */
        $queryFactory = $this->container->getByType('Exports\StoredQueryFactory');
        //$queryFactory->setPresenter(new MockSeriesPresenter());

        $parameters = array(
            'category' => new MockQueryParameter('category'),
        );
        $storedQuery = $queryFactory->createQueryFromSQL(new MockSeriesPresenter(),'SELECT 1, \'ahoj\' FROM dual', $parameters, array('php_post_proc' => 'MockProcessing'));

	// AESOP format requires QID
	$storedQuery->getQueryPattern()->qid = 'aesop.ct';

        $this->fixture = $exportFactory->createFormat(ExportFormatFactory::AESOP, $storedQuery);
    }

    protected function tearDown() {
        parent::tearDown();
    }

    public function testResponse() {
        $response = $this->fixture->getResponse();

        Assert::type('Exports\Formats\PlaintextResponse', $response);
    }

}

class MockSeriesPresenter implements ISeriesPresenter {

    public function getSelectedAcademicYear() {
        return 2000;
    }

    public function getSelectedContest() {
        return (object) array(
                    'contest_id' => 1,
                    'name' => 'FYKOS',
        );
    }

    public function getSelectedSeries() {
        return 1;
    }

    public function getSelectedYear() {
        return 1;
    }

}

class MockQueryParameter {

    public $name;

    function __construct($name) {
        $this->name = $name;
    }

    public function getDefaultValue() {
        return null;
    }

    public function getPDOType() {
        return PDO::PARAM_STR;
    }

}

class MockProcessing extends StoredQueryPostProcessing {

    public function getMaxPoints() {
        return 0;
    }

    public function getDescription() {

    }

    public function processData($data) {
        return $data;
    }

}

$testCase = new AESOPFormatTest($container);
$testCase->run();
