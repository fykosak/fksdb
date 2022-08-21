<?php

declare(strict_types=1);

namespace FKSDB\Tests\ModelsTests\Export\Formats;

$container = require '../../../Bootstrap.php';

use FKSDB\Models\Exports\ExportFormatFactory;
use FKSDB\Models\StoredQuery\StoredQueryFactory;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\DI\Container;
use Tester\Environment;

class AESOPFormatTest extends DatabaseTestCase
{

    private ExportFormatFactory $exportFactory;
    private StoredQueryFactory $queryFactory;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->exportFactory = $this->getContainer()->getByType(ExportFormatFactory::class);
        $this->queryFactory = $this->getContainer()->getByType(StoredQueryFactory::class);
    }

    protected function setUp(): void
    {
        Environment::skip();
        /*global $container;
        parent::setUp();

        //$queryFactory->setPresenter(new MockSeriesPresenter());

        $parameters = [
            'category' => new MockQueryParameter('category'),
        ];
        $storedQuery = $this->queryFactory->createQueryFromSQL(new MockSeriesPresenter($container), 'SELECT 1, \'ahoj\' FROM dual', $parameters, MockProcessing::class);

        // AESOP format requires QID
        $storedQuery->setQId('aesop.ct');

        $this->fixture = $this->exportFactory->createFormat(ExportFormatFactory::AESOP, $storedQuery);*/
    }

    public function testResponse(): void
    {
        /*  $response = $this->fixture->getResponse();
          Assert::type(PlainTextResponse::class, $response);*/
    }
}

$testCase = new AESOPFormatTest($container);
$testCase->run();
