<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\SchoolModel;
use Fykosak\NetteORM\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<SchoolModel,never>
 */
class SchoolColumnFactory extends ColumnFactory
{
    /**
     * @param SchoolModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        return Html::el('span')
            ->addText($model->name_abbrev)
            ->addHtml($model->address->country->getHtmlFlag('ms-2'));
    }
}
