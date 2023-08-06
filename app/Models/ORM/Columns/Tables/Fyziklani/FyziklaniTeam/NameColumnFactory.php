<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Fyziklani\FyziklaniTeam;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ValuePrinters\StringPrinter;
use Fykosak\NetteORM\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<TeamModel2,never>
 */
class NameColumnFactory extends ColumnFactory
{
    public function createFormControl(...$args): BaseControl
    {
        $control = new TextInput($this->getTitle());
        $control->setRequired();
        $control->addRule(
            Form::PATTERN,
            _(
                'Název týmu smí obsahovat pouze latinku, řečtinu, cyrilici
       a ASCII znaky.'
            ),
            '^[\p{Latin}\p{Greek}\p{Cyrillic}\x{0020}-\x{00FF}]+$'
        );
        return $control;
    }

    protected function createHtmlValue(Model $model): Html
    {
        return (new StringPrinter())($model->{$this->modelAccessKey});
    }
}
