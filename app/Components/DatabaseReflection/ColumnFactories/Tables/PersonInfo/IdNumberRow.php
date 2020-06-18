<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * Class IdNumberField
 * *
 */
class IdNumberRow extends AbstractColumnFactory {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Číslo OP');
    }

    /**
     * @return string|null
     */
    public function getDescription()  {
        return _('U cizinců číslo pasu.');
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
        $control = new WriteOnlyInput($this->getTitle());
        $control->setOption('description', $this->getDescription());
        $control->addRule(Form::MAX_LENGTH, null, 32);
        return $control;
    }

    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_FULL;
    }

    protected function getModelAccessKey(): string {
        return 'id_number';
    }

}
