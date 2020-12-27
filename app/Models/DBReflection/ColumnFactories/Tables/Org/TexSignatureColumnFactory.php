<?php

namespace FKSDB\Models\DBReflection\ColumnFactories\Tables\Org;

use FKSDB\Models\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

/**
 * Class TexSignatureRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TexSignatureColumnFactory extends DefaultColumnFactory {

    protected function createFormControl(...$args): BaseControl {
        $control = new TextInput($this->getTitle());

        $control->addRule(Form::MAX_LENGTH, null, 32);
        $control->addCondition(Form::FILLED)
            ->addRule(Form::PATTERN, sprintf(_('%s contains forbidden characters.'), $this->getTitle()), '[a-z][a-z0-9._\-]*');
        return $control;
    }
}
