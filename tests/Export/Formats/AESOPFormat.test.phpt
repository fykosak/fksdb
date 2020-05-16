<?php

$container = require '../../bootstrap.php';

use Exports\ExportFormatFactory;
use Exports\Formats\AESOPFormat;
use Exports\Formats\PlainTextResponse;
use Exports\StoredQueryFactory;
use Exports\StoredQueryPostProcessing;
use FKSDB\CoreModule\ISeriesPresenter;
use Nette\DI\Container;
use Tester\Assert;

class AESOPFormatTest extends DatabaseTestCase {

    /**
     * @var AESOPFormat
     */
    private $fixture;

    protected function setUp() {
        parent::setUp();
        /** @var ExportFormatFactory $exportFactory */
        $exportFactory = $this->getContext()->getByType(ExportFormatFactory::class);
        /** @var StoredQueryFactory $queryFactory */
        $queryFactory = $this->getContext()->getByType(StoredQueryFactory::class);
        //$queryFactory->setPresenter(new MockSeriesPresenter());

        $parameters = [
            'category' => new MockQueryParameter('category'),
        ];
        $storedQuery = $queryFactory->createQueryFromSQL(new MockSeriesPresenter(), 'SELECT 1, \'ahoj\' FROM dual', $parameters, ['php_post_proc' => 'MockProcessing']);

        // AESOP format requires QID
        $storedQuery->getQueryPattern()->qid = 'aesop.ct';

        $this->fixture = $exportFactory->createFormat(ExportFormatFactory::AESOP, $storedQuery);
    }

    protected function tearDown() {
        parent::tearDown();
    }

    public function testResponse() {
        $response = $this->fixture->getResponse();

        Assert::type(PlainTextResponse::class, $response);
    }

}

class MockSeriesPresenter implements ISeriesPresenter {

    public function getSelectedAcademicYear() {
        return 2000;
    }

    public function getSelectedContest() {
        return (object)[
            'contest_id' => 1,
            'name' => 'FYKOS',
        ];
    }

    public function getSelectedSeries() {
        return 1;
    }

    public function getSelectedYear() {
        return 1;
    }

    public function flashMessage($message, $type = 'info') {
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

    public function getDescription(): string {

    }

    public function processData($data) {
        return $data;
    }

}

$testCase = new AESOPFormatTest($container);
$testCase->run();
