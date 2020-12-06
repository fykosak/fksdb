<?php

namespace FKSDB\Tests\ModelTests\Exports\Formats;

$container = require '../../../Bootstrap.php';

use FKSDB\Exports\ExportFormatFactory;
use FKSDB\Exports\Formats\AESOPFormat;
use FKSDB\Exports\Formats\PlainTextResponse;
use FKSDB\ORM\Services\ServiceContest;
use FKSDB\StoredQuery\StoredQueryFactory;
use FKSDB\StoredQuery\StoredQueryPostProcessing;
use FKSDB\Modules\Core\PresenterTraits\ISeriesPresenter;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\StoredQuery\StoredQueryParameter;
use FKSDB\Tests\ModelTests\DatabaseTestCase;
use Nette\DI\Container;
use Tester\Assert;
use Tester\Environment;

class AESOPFormatTest extends DatabaseTestCase {

    private AESOPFormat $fixture;

    protected function setUp(): void {
        Environment::skip('3.0');
        global $container;
        parent::setUp();
        /** @var ExportFormatFactory $exportFactory */
        $exportFactory = $this->getContainer()->getByType(ExportFormatFactory::class);
        /** @var StoredQueryFactory $queryFactory */
        $queryFactory = $this->getContainer()->getByType(StoredQueryFactory::class);
        //$queryFactory->setPresenter(new MockSeriesPresenter());

        $parameters = [
            'category' => new MockQueryParameter('category'),
        ];
        $storedQuery = $queryFactory->createQueryFromSQL(new MockSeriesPresenter($container), 'SELECT 1, \'ahoj\' FROM dual', $parameters, MockProcessing::class);

        // AESOP format requires QID
        $storedQuery->setQId('aesop.ct');

        $this->fixture = $exportFactory->createFormat(ExportFormatFactory::AESOP, $storedQuery);
    }

    protected function tearDown(): void {
        parent::tearDown();
    }

    public function testResponse(): void {
        $response = $this->fixture->getResponse();

        Assert::type(PlainTextResponse::class, $response);
    }

}

class MockSeriesPresenter implements ISeriesPresenter {
    private ModelContest $contest;

    public function __construct(Container $container) {
        $this->contest = $container->getByType(ServiceContest::class)->findByPrimary(1);
    }

    public function getSelectedAcademicYear(): int {
        return 2000;
    }

    /**
     * @return ModelContest|object
     */
    public function getSelectedContest(): ModelContest {
        return $this->contest;
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
     * @return \stdClass
     */
    public function flashMessage($message, string $type = 'info'): \stdClass {
        return new \stdClass();
    }
}

class MockQueryParameter extends StoredQueryParameter {
    public function __construct(string $name) {
        parent::__construct($name, null, \PDO::PARAM_STR);
    }
}

class MockProcessing extends StoredQueryPostProcessing {

    public function getMaxPoints(): int {
        return 0;
    }

    public function getDescription(): string {
        return '';
    }

    public function processData(\PDOStatement $data): \PDOStatement {
        return $data;
    }

}

$testCase = new AESOPFormatTest($container);
$testCase->run();
