<?php

namespace FKSDB\DataTesting\Tests\Person;

use FKSDB\Components\DatabaseReflection\ColumnFactories\ITestedColumnFactory;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Exceptions\BadTypeException;

/**
 * Class PersonFileLevelTest
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class PersonFileLevelTest extends PersonTest {
    /**
     * @var ITestedColumnFactory
     */
    private $rowFactory;
    /**
     * @var string
     */
    private $fieldName;
    /**
     * @var TableReflectionFactory
     */
    private $tableReflectionFactory;

    /**
     * PersonFileLevelTest constructor.
     * @param TableReflectionFactory $tableReflectionFactory
     * @param string $fieldName
     */
    public function __construct(TableReflectionFactory $tableReflectionFactory, string $fieldName) {
        $this->fieldName = $fieldName;
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    /**
     * @return ITestedColumnFactory
     * @throws BadTypeException
     */
    final protected function getRowFactory(): ITestedColumnFactory {
        if (!$this->rowFactory) {
            $this->rowFactory = $this->tableReflectionFactory->loadColumnFactory($this->fieldName);
            if (!$this->rowFactory instanceof ITestedColumnFactory) {
                throw new BadTypeException(ITestedColumnFactory::class, $this->rowFactory);
            }
        }
        return $this->rowFactory;
    }

    /**
     * @return string
     * @throws BadTypeException
     */
    final public function getTitle(): string {
        return $this->getRowFactory()->getTitle();
    }

    final public function getAction(): string {
        return str_replace('.', '__', $this->fieldName);
    }
}
