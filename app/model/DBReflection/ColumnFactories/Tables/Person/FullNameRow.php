<?php

namespace FKSDB\DBReflection\ColumnFactories\Tables\Person;

use FKSDB\DBReflection\ColumnFactories\AbstractColumnException;
use FKSDB\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\ValuePrinters\StringPrinter;
use FKSDB\ORM\Models\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class FullNameRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class FullNameRow extends DefaultColumnFactory {

    /**
     * @param mixed ...$args
     * @return BaseControl
     * @throws AbstractColumnException
     */
    protected function createFormControl(...$args): BaseControl {
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
