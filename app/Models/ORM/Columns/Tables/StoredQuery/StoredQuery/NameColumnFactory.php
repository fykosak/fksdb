<?php

namespace FKSDB\Models\ORM\Columns\Tables\StoredQuery\StoredQuery;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;

/**
 * Class NameColumnFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
class NameColumnFactory extends ColumnFactory {

    protected function createFormControl(...$args): BaseControl {
        $control = new TextInput($this->getTitle());
        $control->addRule(Form::FILLED, _('Název dotazu je třeba vyplnit.'))
            ->addRule(Form::MAX_LENGTH, _('Název dotazu je moc dlouhý.'), 32);
        return $control;
    }
}
