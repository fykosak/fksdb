<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Login;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ValuePrinters\HashPrinter;
use Fykosak\NetteORM\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<LoginModel,never>
 */
class HashColumnFactory extends ColumnFactory
{
    /**
     * @param LoginModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        return (new HashPrinter())($model->hash);
    }
}
