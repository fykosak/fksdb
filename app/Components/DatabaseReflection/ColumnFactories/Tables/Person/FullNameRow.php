<?php

namespace FKSDB\Components\DatabaseReflection\Person;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnException;
use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class FullNameRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class FullNameRow extends AbstractColumnFactory {


    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_ANYBODY, self::PERMISSION_ALLOW_ANYBODY);
    }

    public function getTitle(): string {
        return _('Person');
    }

    /**
     * @param mixed ...$args
     * @return BaseControl
     * @throws AbstractColumnException
     */
    public function createField(...$args): BaseControl {
        throw new AbstractColumnException();
    }

    /**
     * @param AbstractModelSingle|ModelPerson $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->getFullName());
    }

}
