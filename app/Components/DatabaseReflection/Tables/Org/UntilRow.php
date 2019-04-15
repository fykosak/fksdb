<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelOrg;
use Nette\Utils\Html;

/**
 * Class UntilRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class UntilRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Until');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    /**
     * @param AbstractModelSingle|ModelOrg $model
     * @param string $fieldName
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model, string $fieldName): Html {
        if (\is_null($model->until)) {
            return Html::el('span')->addAttributes(['class' => 'badge badge-success'])->addText(_('Still organizes'));
        } else {
            return (new StringPrinter)($model->until);
        }
    }
}
