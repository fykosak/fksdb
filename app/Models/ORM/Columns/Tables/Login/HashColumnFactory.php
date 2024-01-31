<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Login;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\UI\HashPrinter;
use Fykosak\NetteORM\Model\Model;
use Nette\Forms\Controls\BaseControl;
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
        return HashPrinter::getHtml($model->hash);
    }

    /**
     * @throws OmittedControlException
     */
    protected function createFormControl(...$args): BaseControl
    {
        throw new OmittedControlException();
    }
}
