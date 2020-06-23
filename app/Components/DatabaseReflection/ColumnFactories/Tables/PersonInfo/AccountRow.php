<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\Utils\Html;

/**
 * Class AccountRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class AccountRow extends AbstractColumnFactory {

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

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_FULL, self::PERMISSION_ALLOW_FULL);
    }

    protected function getModelAccessKey(): string {
        return 'account';
    }

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->{$this->getModelAccessKey()});
    }
}
