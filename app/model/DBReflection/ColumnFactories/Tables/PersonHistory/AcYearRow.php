<?php

namespace FKSDB\DBReflection\ColumnFactories\PersonHistory;

use FKSDB\DBReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\DBReflection\FieldLevelPermission;
use FKSDB\DBReflection\OmittedControlException;
use FKSDB\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class AcYearRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class AcYearRow extends AbstractColumnFactory {

    public function getTitle(): string {
        return _('Academic year');
    }

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_BASIC, self::PERMISSION_ALLOW_BASIC);
    }

    public function createField(...$args): BaseControl {
        throw new OmittedControlException();
    }

    protected function getModelAccessKey(): string {
        return 'ac_year';
    }

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->{$this->getModelAccessKey()});
    }
}
