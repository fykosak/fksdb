<?php

namespace FKSDB\Models\ORM\Columns\Tables\Org;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

class SinceColumnFactory extends ColumnFactory
{
    protected function createFormControl(...$args): BaseControl
    {
        [$min, $max] = $args;
        if (\is_null($max) || \is_null($min)) {
            throw new \InvalidArgumentException();
        }
        $control = new TextInput($this->getTitle());
        $control->setHtmlType('number');
        $control->addRule(Form::FILLED, _('Field is required'));
        $control->addRule(Form::RANGE, _('First year is not in interval [%d, %d].'), [$min, $max]);
        return $control;
    }
}
