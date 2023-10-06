<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\PersonInfo;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\PersonInfoModel;
use FKSDB\Models\UI\StringPrinter;
use Fykosak\NetteORM\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<PersonInfoModel,never>
 */
class HomepageColumnFactory extends ColumnFactory
{
    protected function createFormControl(...$args): BaseControl
    {
        $control = new TextInput($this->getTitle());
        $control->addCondition(Form::FILLED)->addRule(Form::URL);
        return $control;
    }

    /**
     * @param PersonInfoModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        return StringPrinter::getHtml($model->homepage);
    }
}
