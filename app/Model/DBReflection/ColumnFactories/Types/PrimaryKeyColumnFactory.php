<?php

namespace FKSDB\Model\DBReflection\ColumnFactories\Types;

use FKSDB\Model\DBReflection\OmittedControlException;
use FKSDB\Model\ValuePrinters\StringPrinter;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class PrimaryKeyRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PrimaryKeyColumnFactory extends DefaultColumnFactory {

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())('#' . $model->getPrimary());
    }

    protected function createFormControl(...$args): BaseControl {
        throw new OmittedControlException();
    }
}
