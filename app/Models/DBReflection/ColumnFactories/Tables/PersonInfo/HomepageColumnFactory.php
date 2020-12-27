<?php

namespace FKSDB\Models\DBReflection\ColumnFactories\Tables\PersonInfo;

use FKSDB\Models\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

/**
 * Class HomepageRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class HomepageColumnFactory extends DefaultColumnFactory {

    protected function createFormControl(...$args): BaseControl {
        $control = new TextInput($this->getTitle());
        $control->addCondition(Form::FILLED)->addRule(Form::URL);
        return $control;
    }
}
