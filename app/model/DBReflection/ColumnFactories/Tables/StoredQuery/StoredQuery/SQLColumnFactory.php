<?php

namespace FKSDB\DBReflection\ColumnFactories\StoredQuery\StoredQuery;

use FKSDB\Components\Forms\Controls\SQLConsole;
use FKSDB\DBReflection\ColumnFactories\DefaultColumnFactory;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQuery;
use Nette\Utils\Html;

/**
 * Class SQLColumnFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SQLColumnFactory extends DefaultColumnFactory {
    /**
     * @param AbstractModelSingle|ModelStoredQuery $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('pre')->addAttributes(['class' => 'syntax-sql'])->addText($model->sql);
    }

    protected function createFormControl(...$args): SQLConsole {
        return new SQLConsole($this->getTitle());
    }
}
