<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest\Tests\PersonInfo;

use FKSDB\Components\DataTest\Tests\Test;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Columns\TestedColumnFactory;
use FKSDB\Models\ORM\Models\PersonInfoModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\ReflectionFactory;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-extends Test<PersonInfoModel>
 */
class PersonInfoFileLevelTest extends Test
{
    private string $fieldName;
    private ReflectionFactory $tableReflectionFactory;

    public function __construct(string $fieldName, Container $container)
    {
        parent::__construct($container);
        $this->fieldName = $fieldName;
    }

    public function inject(ReflectionFactory $tableReflectionFactory): void
    {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    /**
     * @throws BadTypeException
     */
    public function getTitle(): Title
    {
        return new Title(null, $this->getRowFactory()->getTitle());
    }

    /**
     * @throws BadTypeException
     * @phpstan-return ColumnFactory<PersonModel,never>&TestedColumnFactory
     */
    final protected function getRowFactory(): TestedColumnFactory
    {
        static $rowFactory;
        if (!isset($rowFactory)) {
            $rowFactory = $this->tableReflectionFactory->loadColumnFactory('person_info', $this->fieldName);
            if (!$rowFactory instanceof TestedColumnFactory) {
                throw new BadTypeException(TestedColumnFactory::class, $rowFactory);
            }
        }
        return $rowFactory;
    }

    /**
     * @param PersonInfoModel $model
     * @throws BadTypeException
     */
    public function run(Logger $logger, Model $model): void
    {
        $this->getRowFactory()->runTest($logger, $model);
    }

    public function getId(): string
    {
        return 'PersonInfo' . $this->fieldName;
    }
}
