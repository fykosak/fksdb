<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\UI\StringPrinter;
use Fykosak\NetteORM\Model;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * @phpstan-template TModel of Model
 * @phpstan-template ArgType
 * @phpstan-extends ColumnFactory<TModel,ArgType>
 */
class TextColumnFactory extends ColumnFactory
{

    protected function createHtmlValue(Model $model): Html
    {
        return StringPrinter::getHtml($model->{$this->modelAccessKey});
    }

    protected function createFormControl(...$args): TextArea
    {
        $control = new TextArea(_($this->getTitle()));
        $meteData = $this->getMetaData();
        if ($meteData['nativetype'] === 'VARCHAR' && $meteData['size']) {
            $control->addRule(Form::MAX_LENGTH, _('Max length reached'), $meteData['size']);
        }
        if ($meteData['nativetype'] === 'CHAR' && $meteData['size']) {
            $control->addRule(Form::LENGTH, _('Max length reached'), $meteData['size']);
        }
        return $control;
    }
}
