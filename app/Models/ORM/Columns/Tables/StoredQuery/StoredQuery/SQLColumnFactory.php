<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\StoredQuery\StoredQuery;

use FKSDB\Components\Forms\Controls\SQLConsole;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\StoredQuery\QueryModel;
use Fykosak\NetteORM\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<QueryModel,never>
 */
class SQLColumnFactory extends ColumnFactory
{
    /**
     * @param QueryModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        return Html::el('pre')->addAttributes(['class' => 'syntax-sql'])->addText($model->sql);
    }

    protected function createFormControl(...$args): SQLConsole
    {
        return new SQLConsole($this->getTitle());
    }
}
