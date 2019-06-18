<?php

namespace FKSDB\Components\DatabaseReflection\PersonHistory;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

/**
 * Class ClassRow
 * @package FKSDB\Components\Forms\Factories\PersonHistory
 */
class ClassRow extends AbstractRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Class');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new TextInput($this->getTitle());
        $control->addRule(Form::MAX_LENGTH, null, 16);
        return $control;
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_RESTRICT;
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'class';
    }
}
