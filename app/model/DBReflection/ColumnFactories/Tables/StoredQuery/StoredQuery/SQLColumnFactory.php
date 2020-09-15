<?php

namespace FKSDB\DBReflection\ColumnFactories\StoredQuery\StoredQuery;

use FKSDB\Components\Forms\Controls\SQLConsole;
use FKSDB\DBReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\DBReflection\FieldLevelPermission;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQuery;
use Nette\Utils\Html;

/**
 * Class SQLColumnFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SQLColumnFactory extends AbstractColumnFactory {
    /**
     * @param AbstractModelSingle|ModelStoredQuery $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('pre')->addAttributes(['class' => 'syntax-sql'])->addText($model->sql);
    }

    public function getTitle(): string {
        return _('SQL');
    }

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_ANYBODY, self::PERMISSION_ALLOW_ANYBODY);
    }

    public function createField(...$args): SQLConsole {
        return new SQLConsole($this->getTitle());
    }
}
