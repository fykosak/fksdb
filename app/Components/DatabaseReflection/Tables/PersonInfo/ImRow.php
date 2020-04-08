<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * Class ImField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class ImRow extends AbstractRow {
    use DefaultPrinterTrait;
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('ICQ, Jabber, apod.');
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
        $control = new  WriteOnlyInput($this->getTitle());
        $control->addRule(Form::MAX_LENGTH, null, 32);
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
        return 'im';
    }

}
