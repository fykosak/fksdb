<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\StoredQuery\StoredQuery;

use FKSDB\Components\Forms\Controls\SQLConsole;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\StoredQuery\ModelStoredQuery;
use Nette\Utils\Html;

class SQLColumnFactory extends ColumnFactory
{
    /**
     * @param ModelStoredQuery $model
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
