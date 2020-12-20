<?php

namespace FKSDB\Models\DBReflection\ColumnFactories\Tables\PersonInfo;

use FKSDB\Models\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnlyInput;
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
