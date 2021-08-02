<?php

namespace FKSDB\Models\DataTesting\Tests\ModelPerson;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Columns\TestedColumnFactory;
use FKSDB\Models\ORM\ORMFactory;
use FKSDB\Models\Exceptions\BadTypeException;

abstract class PersonFileLevelTest extends PersonTest
{

    private TestedColumnFactory $rowFactory;
    private string $fieldName;
    private ORMFactory $tableReflectionFactory;

    /**
     * PersonFileLevelTest constructor.
     * @param ORMFactory $tableReflectionFactory
     * @param string $fieldName
     * @throws BadTypeException
     */
    public function __construct(ORMFactory $tableReflectionFactory, string $fieldName)
    {
        $this->fieldName = $fieldName;
        $this->tableReflectionFactory = $tableReflectionFactory;
        parent::__construct(str_replace('.', '__', $fieldName), $this->getRowFactory()->getTitle());
    }

    /**
     * @return TestedColumnFactory|ColumnFactory
     * @throws BadTypeException
     */
    final protected function getRowFactory(): TestedColumnFactory
    {
        if (!isset($this->rowFactory)) {
            $rowFactory = $this->tableReflectionFactory->loadColumnFactory(...explode('.', $this->fieldName));
            if (!$rowFactory instanceof TestedColumnFactory) {
                throw new BadTypeException(TestedColumnFactory::class, $this->rowFactory);
            }
            $this->rowFactory = $rowFactory;
        }
        return $this->rowFactory;
    }
}
