<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Models\Events\Model\Holder\Field;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;

class PasswordFactory extends AbstractFactory
{

    public function createComponent(Field $field): TextInput
    {
        $element = new TextInput($field->label);
        $element->setHtmlType('password');
        $element->setOption('description', $field->description);
        return $element;
    }

    protected function setDefaultValue(BaseControl $control, Field $field): void
    {
        $control->setDefaultValue('');
    }
}
