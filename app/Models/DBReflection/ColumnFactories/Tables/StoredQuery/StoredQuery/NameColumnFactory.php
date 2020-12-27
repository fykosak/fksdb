<?php

namespace FKSDB\Models\DBReflection\ColumnFactories\Tables\StoredQuery\StoredQuery;

use FKSDB\Models\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;

/**
 * Class NameColumnFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
class NameColumnFactory extends DefaultColumnFactory {

    protected function createFormControl(...$args): BaseControl {
        $control = new TextInput($this->getTitle());
        $control->addRule(Form::FILLED, _('Název dotazu je třeba vyplnit.'))
            ->addRule(Form::MAX_LENGTH, _('Název dotazu je moc dlouhý.'), 32);
        return $control;
    }
}
