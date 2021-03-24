<?php

namespace FKSDB\Models\ORM\Columns\Tables\Fyziklani\FyziklaniTeam;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ValuePrinters\StringPrinter;
use Fykosak\NetteORM\AbstractModel;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class NameRow
 * @author Michal Červeňák <miso@fykos.cz>
 * TODO fix input
 */
class NameColumnFactory extends ColumnFactory {

    protected function createFormControl(...$args): BaseControl {
        $control = new TextInput($this->getTitle());
        $control->addRule(Form::PATTERN, _('Název týmu smí obsahovat pouze latinku, řečtinu, cyrilici a ASCII znaky.'), '/^[\p{Latin}\p{Greek}\p{Cyrillic}\x{0020}-\x{00FF}]+$/u');
        return $control;
    }

    protected function createHtmlValue(AbstractModel $model): Html {
        return (new StringPrinter())($model->{$this->getModelAccessKey()});
    }
}
