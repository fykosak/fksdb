<?php

namespace FKSDB\DataTesting\Tests\Person;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\Forms\Factories\ITestedRowFactory;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use Nette\Application\BadRequestException;

/**
 * Class PersonFileLevelTest
 * @package FKSDB\DataTesting\Tests\Person
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
    private final function loadFactory(TableReflectionFactory $tableReflectionFactory, string $factoryTableName, string $factoryFieldName) {
        $rowFactory = $tableReflectionFactory->loadService($factoryTableName, $factoryFieldName);
        if (!$rowFactory instanceof ITestedRowFactory) {
            throw new BadRequestException();
        }
        $this->rowFactory = $rowFactory;
    }

    /**
     * @return AbstractRow|ITestedRowFactory
     */
    protected final function getRowFactory(): AbstractRow {
        return $this->rowFactory;
    }

    /**
     * @return string
     */
    public final function getTitle(): string {
        return $this->getRowFactory()->getTitle();
    }

    /**
     * @return string
     */
    public final function getAction(): string {
        return $this->actionName;
    }
}
