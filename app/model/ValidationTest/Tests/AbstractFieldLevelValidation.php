<?php

namespace FKSDB\ValidationTest;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\Forms\Factories\ITestedRowFactory;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use Nette\Application\BadRequestException;

/**
 * Class AbstractFieldLevelTest
 * @package FKSDB\ValidationTest
 */
abstract class AbstractFieldLevelValidation extends ValidationTest {
    /**
     * @var AbstractRow|ITestedRowFactory
     */
    private $rowFactory;
    /**
     * @var TableReflectionFactory
     */
    protected $tableReflectionFactory;
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
        $this->tableReflectionFactory = $tableReflectionFactory;
        $this->actionName = $factoryTableName . '__' . $factoryFieldName;
        $this->loadFactory($factoryTableName, $factoryFieldName);
    }

    /**
     * @param string $factoryTableName
     * @param string $factoryFieldName
     * @throws BadRequestException
     * @throws \Exception
     */
    private final function loadFactory(string $factoryTableName, string $factoryFieldName) {
        $rowFactory = $this->tableReflectionFactory->loadService($factoryTableName, $factoryFieldName);
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
