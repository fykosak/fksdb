<?php

namespace FKSDB\DBReflection\ColumnFactories\PersonInfo;

use FKSDB\DBReflection\ColumnFactories\DefaultColumnFactory;
use FKSDB\ValuePrinters\StringPrinter;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class IdNumberRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class IdNumberRow extends DefaultColumnFactory {

    protected function createFormControl(...$args): BaseControl {
        $control = new WriteOnlyInput($this->getTitle());
        $control->setOption('description', $this->getDescription());
        $control->addRule(Form::MAX_LENGTH, null, 32);
        return $control;
    }

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->{$this->getModelAccessKey()});
    }

}
