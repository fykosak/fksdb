<?php

namespace FKSDB\Models\ORM\Columns\ColumnFactories\Tables\PersonInfo;

use FKSDB\Models\ORM\Columns\ColumnFactories\Types\DefaultColumnFactory;
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
