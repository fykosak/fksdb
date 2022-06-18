<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Login;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ValuePrinters\HashPrinter;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\ModelLogin;
use Nette\Utils\Html;

class HashColumnFactory extends ColumnFactory
{

    /**
     * @param ModelLogin $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        return (new HashPrinter())($model->hash);
    }
}
