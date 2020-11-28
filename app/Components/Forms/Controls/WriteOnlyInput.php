<?php

namespace FKSDB\Components\Forms\Controls;

use Nette\Forms\Controls\TextInput;
use Nette\Utils\Html;

/**
 * When user doesn't fill it (i.e. desires original value), it behaves like disabled.
 * Only FILLED validation works properly because there's used special value to distinguish unchanged input.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class WriteOnlyInput extends TextInput implements IWriteOnly {

    use WriteOnlyTrait;

    /**
     * WriteOnlyInput constructor.
     * @param null $label
     * @param null $maxLength
     */
    public function __construct($label = null, $maxLength = null) {
        parent::__construct($label, $maxLength);
        $this->writeOnlyAppendMonitors();
    }

    public function getControl(): Html {
        $control = parent::getControl();
        $control = $this->writeOnlyAdjustControl($control);
        return $control;
    }

    public function loadHttpData(): void {
        parent::loadHttpData();
        $this->writeOnlyLoadHttpData();
    }
}
