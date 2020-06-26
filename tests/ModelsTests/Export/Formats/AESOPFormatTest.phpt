<?php

namespace FKSDB\Tests\ModelTests\Exports\Formats;

$container = require '../../../bootstrap.php';

use FKSDB\Exports\ExportFormatFactory;
use FKSDB\Exports\Formats\AESOPFormat;
use FKSDB\Exports\Formats\PlainTextResponse;
use FKSDB\StoredQuery\StoredQueryFactory;
use FKSDB\StoredQuery\StoredQueryPostProcessing;
use FKSDB\Modules\Core\PresenterTraits\ISeriesPresenter;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\StoredQuery\StoredQueryParameter;
use FKSDB\Tests\ModelTests\DatabaseTestCase;
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
        $storedQuery = $queryFactory->createQueryFromSQL(new MockSeriesPresenter(), 'SELECT 1, \'ahoj\' FROM dual', $parameters, MockProcessing::class);

        // AESOP format requires QID
        $storedQuery->setQId('aesop.ct');

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

    public function getSelectedAcademicYear(): int {
        return 2000;
    }

    /**
     * @return ModelContest|object
     */
    public function getSelectedContest() {
        return (object)[
            'contest_id' => 1,
            'name' => 'FYKOS',
        ];
    }

    public function getSelectedSeries(): int {
        return 1;
    }

    public function getSelectedYear(): int {
        return 1;
    }

    /**
     * @param string $message
     * @param string $type
     * @return void
     */
    public function flashMessage($message, $type = 'info') {
    }
}

class MockQueryParameter extends StoredQueryParameter {
    public function __construct($name) {
        parent::__construct($name, null, \PDO::PARAM_STR);
    }
}

class MockProcessing extends StoredQueryPostProcessing {

    public function getMaxPoints() {
        return 0;
    }

    public function getDescription(): string {
        return '';
    }

    public function processData(\PDOStatement $data) {
        return $data;
    }

}

$testCase = new AESOPFormatTest($container);
$testCase->run();
