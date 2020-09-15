<?php

namespace FKSDB\DBReflection\ColumnFactories\PersonInfo;

use FKSDB\DBReflection\ColumnFactories\DefaultColumnFactory;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * Class ImRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ImRow extends DefaultColumnFactory {

    protected function createFormControl(...$args): BaseControl {
        $control = new  WriteOnlyInput($this->getTitle());
        $control->addRule(Form::MAX_LENGTH, null, 32);
        return $control;
    }
}
