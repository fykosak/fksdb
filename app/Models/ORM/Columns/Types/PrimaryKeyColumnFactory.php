<?php

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ValuePrinters\StringPrinter;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class PrimaryKeyRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PrimaryKeyColumnFactory extends ColumnFactory {
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())('#' . $model->getPrimary());
    }

    protected function createFormControl(...$args): BaseControl {
        throw new OmittedControlException();
    }
}
