<?php

namespace FKSDB\DataTesting\Tests\Person;

use FKSDB\DBReflection\ColumnFactories\ITestedColumnFactory;
use FKSDB\DBReflection\DBReflectionFactory;
use FKSDB\Exceptions\BadTypeException;

/**
 * Class PersonFileLevelTest
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class PersonFileLevelTest extends PersonTest {

    private ITestedColumnFactory $rowFactory;

    private string $fieldName;

    private DBReflectionFactory $tableReflectionFactory;

    /**
     * PersonFileLevelTest constructor.
     * @param DBReflectionFactory $tableReflectionFactory
     * @param string $fieldName
     * @throws BadTypeException
     */
    public function __construct(DBReflectionFactory $tableReflectionFactory, string $fieldName) {
        $this->fieldName = $fieldName;
        $this->tableReflectionFactory = $tableReflectionFactory;
        parent::__construct(str_replace('.', '__', $fieldName), $this->getRowFactory()->getTitle());
    }

    /**
     * @return ITestedColumnFactory
     * @throws BadTypeException
     */
    final protected function getRowFactory(): ITestedColumnFactory {
        if (!isset($this->rowFactory)) {
            $rowFactory = $this->tableReflectionFactory->loadColumnFactory($this->fieldName);
            if (!$rowFactory instanceof ITestedColumnFactory) {
                throw new BadTypeException(ITestedColumnFactory::class, $this->rowFactory);
            }
            $this->rowFactory = $rowFactory;
        }
        return $this->rowFactory;
    }
}
