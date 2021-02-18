<?php

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Models\Events\Model\Holder\Field;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PasswordFactory extends AbstractFactory {

    public function createComponent(Field $field): TextInput {
        $element = new TextInput($field->getLabel());
        $element->setHtmlType('password');
        $element->setOption('description', $field->getDescription());
        return $element;
    }

    protected function setDefaultValue(BaseControl $control, Field $field): void {
        $control->setDefaultValue('');
    }
}
