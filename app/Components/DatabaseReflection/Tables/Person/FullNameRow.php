<?php

namespace FKSDB\Components\DatabaseReflection\Person;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\AbstractRowException;
use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class FullNameRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class FullNameRow extends AbstractRow {


    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    public function getTitle(): string {
        return _('Person');
    }

    /**
     * @param mixed ...$args
     * @return BaseControl
     * @throws AbstractRowException
     */
    public function createField(...$args): BaseControl {
        throw new AbstractRowException();
    }

    /**
     * @param AbstractModelSingle|ModelPerson $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->getFullName());
    }

}
