<?php

namespace FKSDB\Components\DatabaseReflection\Person;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\ValuePrinters\DatePrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Utils\Html;

/**
 * Class CreatedRow
 * @package FKSDB\Components\DatabaseReflection\Person
 */
class CreatedRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Created');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_RESTRICT;
    }

    /**
     * @param AbstractModelSingle|ModelPerson $model
     * @param string $fieldName
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model, string $fieldName): Html {
        return (new DatePrinter('c'))($model->created);
    }
}
