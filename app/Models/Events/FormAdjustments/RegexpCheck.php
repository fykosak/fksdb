<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\FormAdjustments;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use Nette\Forms\Form;
use Nette\Forms\Control;
use Nette\Utils\Strings;

class RegexpCheck extends AbstractAdjustment
{

    private string $field;
    private string $message;
    private string $pattern;

    public function __construct(string $field, string $message, string $pattern)
    {
        $this->field = $field;
        $this->message = $message;
        $this->pattern = $pattern;
    }

    protected function innerAdjust(Form $form, BaseHolder $holder): void
    {
        $controls = $this->getControl($this->field);
        if (!$controls) {
            return;
        }
        foreach ($controls as $control) {
            $control->addRule(
                fn(Control $control): bool => (bool)Strings::match($control->getValue(), $this->pattern),
                $this->message
            );
        }
    }
}
