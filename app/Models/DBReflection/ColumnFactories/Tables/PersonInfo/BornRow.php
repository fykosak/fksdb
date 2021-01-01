<?php

namespace FKSDB\Models\DBReflection\ColumnFactories\Tables\PersonInfo;

use FKSDB\Models\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Models\ValuePrinters\DatePrinter;
use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnlyDatePicker;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelPersonInfo;
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
