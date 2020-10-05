<?php

namespace FKSDB\DBReflection\ColumnFactories\PersonInfo;

use FKSDB\DBReflection\ColumnFactories\DefaultColumnFactory;
use FKSDB\ValuePrinters\DatePrinter;
use FKSDB\Components\Forms\Controls\WriteOnlyDatePicker;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPersonInfo;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class BornRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class BornRow extends DefaultColumnFactory {

    protected function createFormControl(...$args): BaseControl {
        $control = new WriteOnlyDatePicker($this->getTitle());
        $control->setDefaultValue((new \DateTime())->modify('-16 years'));
        return $control;
    }

    /**
     * @param AbstractModelSingle|ModelPersonInfo $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new DatePrinter('d.m.Y'))($model->born);
    }
}
