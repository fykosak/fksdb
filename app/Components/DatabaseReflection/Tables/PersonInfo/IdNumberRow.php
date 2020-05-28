<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * Class IdNumberField
 * *
 */
class IdNumberRow extends AbstractRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Číslo OP');
    }

    /**
     * @return null|string
     */
    public function getDescription() {
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

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_FULL;
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'id_number';
    }

}
