<?php

namespace FKSDB\Tests\ModelTests\Exports\Formats;

$container = require '../../../bootstrap.php';

use Exports\ExportFormatFactory;
use Exports\Formats\AESOPFormat;
use Exports\Formats\PlainTextResponse;
use Exports\StoredQueryFactory;
use Exports\StoredQueryPostProcessing;
use FKSDB\Modules\Core\PresenterTraits\ISeriesPresenter;
use FKSDB\ORM\Models\ModelContest;
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
        $storedQuery = $queryFactory->createQueryFromSQL(new MockSeriesPresenter(), 'SELECT 1, \'ahoj\' FROM dual', $parameters, ['php_post_proc' => MockProcessing::class]);

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

class MockQueryParameter {

    public $name;

    public function __construct($name) {
        $this->name = $name;
    }

    public function getDefaultValue() {
        return null;
    }

    public function getPDOType() {
        return \PDO::PARAM_STR;
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
