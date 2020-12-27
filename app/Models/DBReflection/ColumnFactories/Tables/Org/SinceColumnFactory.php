<?php

namespace FKSDB\Models\DBReflection\ColumnFactories\Tables\Org;

use FKSDB\Models\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

/**
 * Class SinceRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SinceColumnFactory extends DefaultColumnFactory {

    protected function createFormControl(...$args): BaseControl {
        [$min, $max] = $args;
        if (\is_null($max) || \is_null($min)) {
            throw new \InvalidArgumentException();
        }
        $control = new TextInput($this->getTitle());
        $control->addRule(Form::NUMERIC);
        $control->addRule(Form::FILLED);
        $control->addRule(Form::RANGE, _('First year is not in interval [%d, %d].'), [$min, $max]);
        return $control;
    }
}
