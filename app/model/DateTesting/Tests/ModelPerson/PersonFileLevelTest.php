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
    /** @var ITestedColumnFactory */
    private $rowFactory;
    /** @var string */
    private $fieldName;
    /** @var DBReflectionFactory */
    private $tableReflectionFactory;

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
        if (!$this->rowFactory) {
            $this->rowFactory = $this->tableReflectionFactory->loadColumnFactory($this->fieldName);
            if (!$this->rowFactory instanceof ITestedColumnFactory) {
                throw new BadTypeException(ITestedColumnFactory::class, $this->rowFactory);
            }
        }
        return $this->rowFactory;
    }
}
