<?php

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ValuePrinters\StringPrinter;
use Fykosak\NetteORM\AbstractModel;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

class PrimaryKeyColumnFactory extends ColumnFactory {
    protected function createHtmlValue(AbstractModel $model): Html {
        return (new StringPrinter())('#' . $model->getPrimary());
    }

    protected function createFormControl(...$args): BaseControl {
        throw new OmittedControlException();
    }
}
