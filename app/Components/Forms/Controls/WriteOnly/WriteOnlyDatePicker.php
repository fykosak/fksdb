<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\WriteOnly;

use FKSDB\Components\Forms\Controls\DateInputs\DateInput;
use Nette\Utils\Html;

/**
 * When user doesn't fill it (i.e. desires original value), it behaves like disabled.
 * Only FILLED validation works properly because there's used special value to distinguish unchanged input.
 */
class WriteOnlyDatePicker extends DateInput implements WriteOnly
{
    use WriteOnlyTrait;

    public function __construct(?string $label = null)
    {
        parent::__construct($label);
        $this->writeOnlyAppendMonitors();
    }

    public function getControl(): Html
    {
        $control = parent::getControl();
        return $this->writeOnlyAdjustControl($control);
    }

    /**
     * @param mixed $value
     * @return static
     * @throws \Exception
     */
    public function setValue($value): self
    {
        if ($value == self::VALUE_ORIGINAL) {
            $this->value = $value;
        } else {
            parent::setValue($value);
        }
        return $this;
    }

    public function loadHttpData(): void
    {
        parent::loadHttpData();
        $this->writeOnlyLoadHttpData();
    }
}
