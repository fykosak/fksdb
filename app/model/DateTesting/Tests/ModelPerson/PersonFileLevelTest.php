<?php

namespace FKSDB\DataTesting\Tests\Person;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\Forms\Factories\ITestedRowFactory;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Exceptions\BadTypeException;
use Nette\Application\BadRequestException;

/**
 * Class PersonFileLevelTest
 * *
 */
abstract class PersonFileLevelTest extends PersonTest {
    /**
     * @var AbstractRow|ITestedRowFactory
     */
    private $rowFactory;
    /**
     * @var string
     */
    private $actionName;

    /**
     * AbstractPhoneNumber constructor.
     * @param TableReflectionFactory $tableReflectionFactory
     * @param string $factoryTableName
     * @param string $factoryFieldName
     * @throws BadRequestException
     */
    public function __construct(TableReflectionFactory $tableReflectionFactory, string $factoryTableName, string $factoryFieldName) {
        $this->actionName = $factoryTableName . '__' . $factoryFieldName;
        $this->loadFactory($tableReflectionFactory, $factoryTableName, $factoryFieldName);
    }

    /**
     * @param TableReflectionFactory $tableReflectionFactory
     * @param string $factoryTableName
     * @param string $factoryFieldName
     * @throws BadRequestException
     * @throws \Exception
     */
    final private function loadFactory(TableReflectionFactory $tableReflectionFactory, string $factoryTableName, string $factoryFieldName) {
        $rowFactory = $tableReflectionFactory->loadService($factoryTableName, $factoryFieldName);
        if (!$rowFactory instanceof ITestedRowFactory) {
            throw new BadTypeException(ITestedRowFactory::class, $rowFactory);
        }
        $this->rowFactory = $rowFactory;
    }

    /**
     * @return AbstractRow|ITestedRowFactory
     */
    final protected function getRowFactory(): AbstractRow {
        return $this->rowFactory;
    }

    final public function getTitle(): string {
        return $this->getRowFactory()->getTitle();
    }

    final public function getAction(): string {
        return $this->actionName;
    }
}
