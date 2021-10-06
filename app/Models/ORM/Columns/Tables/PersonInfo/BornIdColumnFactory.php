<?php

namespace FKSDB\Models\ORM\Columns\Tables\PersonInfo;

use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnlyInput;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Components\Forms\Rules\BornNumber;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

class BornIdColumnFactory extends ColumnFactory {

    protected function createFormControl(...$args): BaseControl {
        $control = new WriteOnlyInput($this->getTitle());
        $control->setOption('description', $this->getDescription());
        $control->addCondition(Form::FILLED)
            ->addRule(new BornNumber(), _('Invalid bornID format'));
        return $control;
    }
}
