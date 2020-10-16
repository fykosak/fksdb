<?php

namespace FKSDB\DBReflection\ColumnFactories\PersonInfo;

use FKSDB\DBReflection\ColumnFactories\DefaultColumnFactory;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use FKSDB\Components\Forms\Rules\BornNumber;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * Class BornIdRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class BornIdRow extends DefaultColumnFactory {
    /**
     * @param array $args
     * @return BaseControl
     */
    protected function createFormControl(...$args): BaseControl {
        $control = new WriteOnlyInput($this->getTitle());
        $control->setOption('description', $this->getDescription());
        $control->addCondition(Form::FILLED)
            ->addRule(new BornNumber(), _('Rodné číslo nemá platný formát.'));
        return $control;
    }
}
