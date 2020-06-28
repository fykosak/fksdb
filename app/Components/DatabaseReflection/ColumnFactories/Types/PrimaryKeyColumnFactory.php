<?php

namespace FKSDB\Components\DatabaseReflection\ColumnFactories;

use FKSDB\Components\DatabaseReflection\OmittedControlException;
use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Application\BadRequestException;
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

    public function createFormControl(...$args): BaseControl {
        throw new OmittedControlException();
    }
}
