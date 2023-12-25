<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\WriteOnly;

use Nette\Forms\Controls\TextInput;
use Nette\Utils\Html;

/**
 * When user doesn't fill it (i.e. desires original value), it behaves like disabled.
 * Only FILLED validation works properly because there's used special value to distinguish unchanged input.
 */
class WriteOnlyInput extends TextInput implements WriteOnly
{
    use WriteOnlyTrait;

    /**
     * @param object|string|null $label
     */
    public function __construct($label = null, int $maxLength = null)
    {
        parent::__construct($label, $maxLength);
        $this->writeOnlyAppendMonitors();
    }

    public function getControl(): Html
    {
        $control = parent::getControl();
        return $this->writeOnlyAdjustControl($control);
    }

    public function loadHttpData(): void
    {
        parent::loadHttpData();
        $this->writeOnlyLoadHttpData();
    }
}
