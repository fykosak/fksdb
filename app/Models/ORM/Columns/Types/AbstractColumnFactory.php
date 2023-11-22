<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Models\ORM\Columns\AbstractColumnException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use Nette\Forms\Controls\BaseControl;

/**
 * @phpstan-template TModel of \Fykosak\NetteORM\Model\Model
 * @phpstan-extends ColumnFactory<TModel,never>
 */
abstract class AbstractColumnFactory extends ColumnFactory
{
    /**
     * @throws AbstractColumnException
     */
    final protected function createFormControl(...$args): BaseControl
    {
        throw new AbstractColumnException($this->tableName, $this->modelAccessKey);
    }
}
