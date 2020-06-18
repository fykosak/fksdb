<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use FKSDB\Components\Forms\Rules\BornNumber;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * Class BornIdField
 * *
 */
class BornIdRow extends AbstractColumnFactory {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Rodné číslo');
    }

    /**
     * @return string|null
     */
    public function getDescription() {
        return _('U cizinců prázdné.');
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
        $control = new WriteOnlyInput($this->getTitle());
        $control->setOption('description', $this->getDescription());
        $control->addCondition(Form::FILLED)
            ->addRule(new BornNumber(), _('Rodné číslo nemá platný formát.'));
        return $control;
    }

    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_FULL;
    }

    protected function getModelAccessKey(): string {
        return 'born_id';
    }
}
