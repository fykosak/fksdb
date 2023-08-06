<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Fyziklani\FyziklaniTeam;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ValuePrinters\HashPrinter;
use Fykosak\NetteORM\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<TeamModel2,never>
 */
class PasswordColumnFactory extends ColumnFactory
{
    /**
     * @param TeamModel2 $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        return (new HashPrinter())($model->password);
    }

    protected function createFormControl(...$args): BaseControl
    {
        $control = new TextInput($this->getTitle());
        $control->setHtmlType('password');
        return $control;
    }
}
