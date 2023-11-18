<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Person;

use FKSDB\Models\ORM\Columns\Types\AbstractColumnFactory;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\UI\StringPrinter;
use Fykosak\NetteORM\Model\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends AbstractColumnFactory<PersonModel,never>
 */
class FullNameColumnFactory extends AbstractColumnFactory
{
    /**
     * @param PersonModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        return StringPrinter::getHtml($model->getFullName());
    }
}
