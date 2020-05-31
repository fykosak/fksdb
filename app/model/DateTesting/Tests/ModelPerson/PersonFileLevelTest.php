<?php

namespace FKSDB\DataTesting\Tests\Person;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\Forms\Factories\ITestedRowFactory;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Exceptions\BadTypeException;

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
     * @param string $factoryName
     * @throws BadTypeException
     */
    public function __construct(TableReflectionFactory $tableReflectionFactory, string $factoryName) {
        $this->actionName = str_replace('.', '__', $factoryName);
        $this->loadFactory($tableReflectionFactory, $factoryName);
    }

    /**
     * @param TableReflectionFactory $tableReflectionFactory
     * @param string $factoryName
     * @throws BadTypeException
     */
    final private function loadFactory(TableReflectionFactory $tableReflectionFactory, string $factoryName) {
        $rowFactory = $tableReflectionFactory->loadRowFactory($factoryName);
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
