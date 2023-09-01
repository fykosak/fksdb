<?php

declare(strict_types=1);

namespace FKSDB\Models\DataTesting\Tests\ModelPerson;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Columns\TestedColumnFactory;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\ORMFactory;

abstract class PersonFileLevelTest extends PersonTest
{
    private string $fieldName;
    private ORMFactory $tableReflectionFactory;

    /**
     * @throws BadTypeException
     */
    public function __construct(ORMFactory $tableReflectionFactory, string $fieldName)
    {
        $this->fieldName = $fieldName;
        $this->tableReflectionFactory = $tableReflectionFactory;
        parent::__construct(str_replace('.', '__', $fieldName), $this->getRowFactory()->getTitle());
    }

    /**
     * @throws BadTypeException
     * @phpstan-return ColumnFactory<PersonModel,never>&TestedColumnFactory
     */
    final protected function getRowFactory(): TestedColumnFactory
    {
        static $rowFactory;
        if (!isset($rowFactory)) {
            $rowFactory = $this->tableReflectionFactory->loadColumnFactory(...explode('.', $this->fieldName));
            if (!$rowFactory instanceof TestedColumnFactory) {
                throw new BadTypeException(TestedColumnFactory::class, $rowFactory);
            }
        }
        return $rowFactory;
    }
}
