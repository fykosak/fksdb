<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use Nette\Forms\Form;
use Nette\Forms\IControl;

/**
 * Class AccountField
 * *
 */
class AccountRow extends AbstractColumnFactory {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Bank account');
    }

    /**
     * @return WriteOnlyInput
     */
    public function creteField(): IControl {
        $control = new WriteOnlyInput($this->getTitle());
        $control->addRule(Form::MAX_LENGTH, null, 32);
        return $control;
    }

    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_FULL;
    }

    protected function getModelAccessKey(): string {
        return 'account';
    }
}
