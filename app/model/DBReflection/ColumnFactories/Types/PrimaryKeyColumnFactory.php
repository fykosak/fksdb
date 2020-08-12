<?php

namespace FKSDB\DBReflection\ColumnFactories;

use FKSDB\DBReflection\OmittedControlException;
use FKSDB\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
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
