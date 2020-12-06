<?php

namespace FKSDB\Tests\ModelsTests\Export\Formats;

$container = require '../../../Bootstrap.php';

use FKSDB\Exports\ExportFormatFactory;
use FKSDB\Exports\Formats\AESOPFormat;
use FKSDB\Exports\Formats\PlainTextResponse;
use FKSDB\StoredQuery\StoredQueryFactory;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\DI\Container;
use Tester\Assert;

class AESOPFormatTest extends DatabaseTestCase {

    private ExportFormatFactory $exportFactory;
    private StoredQueryFactory $queryFactory;

    private AESOPFormat $fixture;

    public function __construct(Container $container) {
        parent::__construct($container);
        $this->exportFactory = $this->getContainer()->getByType(ExportFormatFactory::class);
        $this->queryFactory = $this->getContainer()->getByType(StoredQueryFactory::class);
    }

    protected function setUp(): void {
        global $container;
        parent::setUp();

        //$queryFactory->setPresenter(new MockSeriesPresenter());

        $parameters = [
            'category' => new MockQueryParameter('category'),
        ];
        $storedQuery = $this->queryFactory->createQueryFromSQL(new MockSeriesPresenter($container), 'SELECT 1, \'ahoj\' FROM dual', $parameters, MockProcessing::class);

        // AESOP format requires QID
        $storedQuery->setQId('aesop.ct');

        $this->fixture = $this->exportFactory->createFormat(ExportFormatFactory::AESOP, $storedQuery);
    }

    protected function tearDown(): void {
        parent::tearDown();
    }

    public function testResponse(): void {
        $response = $this->fixture->getResponse();
        Assert::type(PlainTextResponse::class, $response);
    }
}

$testCase = new AESOPFormatTest($container);
$testCase->run();
