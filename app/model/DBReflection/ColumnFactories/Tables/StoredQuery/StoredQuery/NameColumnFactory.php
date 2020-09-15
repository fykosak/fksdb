<?php

namespace FKSDB\DBReflection\ColumnFactories\StoredQuery\StoredQuery;

use FKSDB\DBReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\DBReflection\FieldLevelPermission;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ValuePrinters\StringPrinter;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\Html;

/**
 * Class NameColumnFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
class NameColumnFactory extends AbstractColumnFactory {

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->name);
    }

    public function getTitle(): string {
        return _('Query name');
    }

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_ANYBODY, self::PERMISSION_ALLOW_ANYBODY);
    }

    public function createField(...$args): BaseControl {
        $control = new TextInput($this->getTitle());
        $control->addRule(Form::FILLED, _('Název dotazu je třeba vyplnit.'))
            ->addRule(Form::MAX_LENGTH, _('Název dotazu je moc dlouhý.'), 32);
        return $control;
    }
}
